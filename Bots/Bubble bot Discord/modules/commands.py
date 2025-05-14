import asyncio
import discord
from discord.ext import commands
from modules.config import get_server_config, save_config
from modules.database import MONGODB_CONNECTION_STRING, fetch_or_register_user
from modules.decorators import check_admin, check_blacklist, check_category
import os
import random
from datetime import datetime, timedelta
import json
import pymongo


def load_cooldowns():
    try:
        with open('cooldowns.json', 'r', encoding='utf-8') as f:
            return json.load(f)
    except (FileNotFoundError, json.JSONDecodeError):
        return []

def save_cooldowns(new_entries):
    existing_cooldowns = load_cooldowns()
    cooldowns_to_add = [entry for entry in new_entries if not any(
        existing['user_id'] == entry['user_id'] and 
        existing['command_used'] == entry['command_used'] and 
        existing['time'] == entry['time'] 
        for existing in existing_cooldowns
    )]
    existing_cooldowns.extend(cooldowns_to_add)
    with open('cooldowns.json', 'w', encoding='utf-8') as f:
        json.dump(existing_cooldowns, f, indent=4, default=str, ensure_ascii=False)
    with open('cooldowns.txt', 'w', encoding='utf-8') as f:
        for entry in existing_cooldowns:
            f.write(f"User ID: {entry['user_id']}\nUser Name: {entry['user_name']}\nCommand Used: {entry['command_used']}\nTime: {entry['time']}\nCooldown Time: {entry['cooldown_time']}\nFile Name: {entry['file_name']}\n\n")

async def can_use_command(user_id: int, command_name: str, last_command_usage: list, cooldown_hours: int) -> tuple:
    cooldown_duration = timedelta(hours=cooldown_hours)
    now = datetime.now()
    for entry in last_command_usage:
        if entry['user_id'] == user_id and entry['command_used'] == command_name:
            last_usage_time = datetime.strptime(entry['time'], "%Y-%m-%d %H:%M:%S")
            time_passed = now - last_usage_time
            if time_passed < cooldown_duration:
                remaining_time = cooldown_duration - time_passed
                remaining_hours = remaining_time.total_seconds() // 3600
                remaining_minutes = (remaining_time.total_seconds() % 3600) // 60
                return False, int(remaining_hours), int(remaining_minutes)
    return True, 0, 0

async def log_command_usage(bot, ctx, command_name: str, file_name: str):
    server_config = get_server_config(ctx.guild.id)
    log_channel = bot.get_channel(server_config['bot_log'])
    if log_channel:
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        await log_channel.send(f"{ctx.author.mention} used `{command_name}` command and received `{file_name}` at {timestamp} UTC")

async def handle_cookie_command(bot, ctx, directory: str, command_name: str, cooldown_hours: int, last_command_usage: list):
    user = ctx.author if hasattr(ctx, 'author') else ctx.user
    server_config = get_server_config(ctx.guild.id)
    if str(user.id) in server_config.get('whitelist', {}):
        await execute_whitelisted_command(bot, ctx, directory, command_name)
    else:
        await execute_regular_command(bot, ctx, directory, command_name, cooldown_hours, last_command_usage)

async def execute_whitelisted_command(bot, ctx, directory: str, command_name: str):
    files = [file for file in os.listdir(directory) if file.endswith('.txt')]
    if not files:
        await ctx.send(f"No {command_name} cookie files are available right now. Please try again later.", ephemeral=True)
        return

    random_file = random.choice(files)
    file_path = os.path.join(directory, random_file)
    await ctx.send(f"Sending you a random cookie file: `{random_file}`", ephemeral=True)
    try:
        await ctx.author.send(file=discord.File(file_path))
    except discord.errors.Forbidden:
        await ctx.send("I cannot send you a direct message. Please enable DMs and try again.", ephemeral=True)
        return
    await log_command_usage(bot, ctx, command_name, random_file)

async def execute_regular_command(bot, ctx, directory: str, command_name: str, cooldown_hours: int, last_command_usage: list):
    user_id = ctx.author.id if hasattr(ctx, 'author') else ctx.user.id
    user_data = fetch_or_register_user(user_id)
    if not user_data:
        await ctx.send("Failed to fetch or register user.", ephemeral=True)
        return

    user = bot.get_user(user_id)
    if not user:
        await ctx.send("Failed to fetch Discord user.", ephemeral=True)
        return

    can_use, remaining_hours, remaining_minutes = await can_use_command(user_id, command_name, last_command_usage, cooldown_hours)
    if not can_use:
        await ctx.send(f"You can use this command only once every {cooldown_hours} hours. Now {remaining_hours} hours {remaining_minutes} minutes remaining.", ephemeral=True)
        return

    current_points = user_data.get('points', 0)
    if current_points < 2:
        await ctx.send("You don't have enough points to use this command.", ephemeral=True)
        return

    new_points = max(current_points - 2, 0)
    client = pymongo.MongoClient(MONGODB_CONNECTION_STRING)
    try:
        db = client['db_discord']
        collection = db['tbl_discord']
        collection.update_one({'userid': user_id}, {'$set': {'points': new_points}})
        await ctx.send(f"Your 2 points have been deducted. Your remaining points: {new_points}.", ephemeral=True)

        files = [file for file in os.listdir(directory) if file.endswith('.txt')]
        if not files:
            await ctx.send(f"No {command_name} cookie files are available right now. Please try again later.", ephemeral=True)
            return

        random_file = random.choice(files)
        file_path = os.path.join(directory, random_file)
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        cooldown_time = (datetime.now() + timedelta(hours=cooldown_hours)).strftime("%Y-%m-%d %H:%M:%S")
        new_entry = {
            'user_id': user_id,
            'user_name': ctx.author.name if hasattr(ctx, 'author') else ctx.user.name,
            'command_used': command_name,
            'time': timestamp,
            'cooldown_time': cooldown_time,
            'file_name': random_file
        }
        last_command_usage.append(new_entry)
        save_cooldowns(last_command_usage)
        try:
            await user.send(file=discord.File(file_path))
            await log_command_usage(bot, ctx, command_name, random_file)
        except discord.errors.Forbidden:
            await ctx.send("I cannot send you a direct message. Please enable DMs and try again.", ephemeral=True)
    except Exception as e:
        await ctx.send(f"Failed to deduct points or send cookie: {e}", ephemeral=True)
    finally:
        client.close()

def create_cookie_command(bot, command_name, directory_key, description):
    @commands.hybrid_command(name=command_name, description=description)
    @check_blacklist()
    @check_category()
    async def cookie_command(ctx):
        server_config = get_server_config(ctx.guild.id)
        directory = server_config['directories'].get(directory_key)
        if directory:
            last_command_usage = load_cooldowns()
            await handle_cookie_command(bot, ctx, directory, command_name, server_config['command_cooldown_hours'], last_command_usage)
        else:
            await ctx.send(f"The directory '{directory_key}' is not configured or does not exist.", ephemeral=True)
    bot.add_command(cookie_command)

async def register_commands(bot):
    create_cookie_command(bot, "netflixcookie", 'netflix', "Get a random Netflix cookie!")
    create_cookie_command(bot, "spotifycookie", 'spotify', "Get a random Spotify cookie!")
    create_cookie_command(bot, "primecookie", 'prime', "Get a random Prime Video cookie!")
    # create_cookie_command(bot, "hulucookie", 'hulu', "Get a random Hulu cookie!")
    # create_cookie_command(bot, "mubicookie", 'mubi', "Get a random Mubi cookie!")
    # create_cookie_command(bot, "jiosavancookie", 'jio savan', "Get a random Mubi cookie!")


    @commands.hybrid_command(name="hello", description="Say hello to the bot")
    @check_blacklist()
    @check_category()
    async def say_hello(ctx):
        await ctx.send("Hello! I hope you're having a great day.", ephemeral=True)
    bot.add_command(say_hello)

    @commands.hybrid_command(name="whitelist", description="Whitelist a user to use commands")
    @check_admin()
    async def whitelist_user(ctx, user: discord.Member):
        server_config = get_server_config(ctx.guild.id)
        if 'whitelist' not in server_config:
            server_config['whitelist'] = {}
        server_config['whitelist'][str(user.id)] = {'remaining_uses': float('inf')}
        save_config()
        await ctx.send(f"User {user.mention} has been whitelisted.", ephemeral=True)
    bot.add_command(whitelist_user)

    @commands.hybrid_command(name="blacklist", description="Blacklist a user from using commands")
    @check_admin()
    async def blacklist_user(ctx, user: discord.Member):
        server_config = get_server_config(ctx.guild.id)
        if user.id not in server_config['blacklist_uids']:
            server_config['blacklist_uids'].append(user.id)
            save_config()
            await ctx.send(f"User {user.mention} has been blacklisted.", ephemeral=True)
        else:
            await ctx.send(f"User {user.mention} is already blacklisted.", ephemeral=True)
    bot.add_command(blacklist_user)

    @commands.hybrid_command(name="removeblacklist", description="Remove a user from the blacklist")
    @check_admin()
    async def remove_blacklist_user(ctx, user: discord.Member):
        server_config = get_server_config(ctx.guild.id)
        if user.id in server_config['blacklist_uids']:
            server_config['blacklist_uids'].remove(user.id)
            save_config()
            await ctx.send(f"User {user.mention} has been removed from the blacklist.", ephemeral=True)
        else:
            await ctx.send(f"User {user.mention} is not currently blacklisted.", ephemeral=True)
    bot.add_command(remove_blacklist_user)

    @commands.hybrid_command(name="stock", description="Check stock of .txt files in specified category")
    @check_blacklist()
    @check_category()
    async def check_stock(ctx, category: str):
        server_config = get_server_config(ctx.guild.id)
        directories = server_config['directories']
        response = ""
        if category.lower() == "all":
            for key, directory in directories.items():
                if os.path.exists(directory):
                    file_count = len([file for file in os.listdir(directory) if file.endswith('.txt')])
                    response += f"{key.capitalize()}: {file_count} files\n"
                else:
                    response += f"{key.capitalize()}: Directory not found\n"
        elif category.lower() in directories:
            directory = directories[category.lower()]
            if os.path.exists(directory):
                file_count = len([file for file in os.listdir(directory) if file.endswith('.txt')])
                response = f"{category.capitalize()}: {file_count} files"
            else:
                response = f"{category.capitalize()}: Directory not found"
        else:
            response = "Invalid category specified. Available options: all, netflix, spotify, crunchyroll, prime"
        await ctx.send(response, ephemeral=True)
    bot.add_command(check_stock)

    @commands.hybrid_command(name='link', description="Gives you a link to claim your points")
    async def link(ctx):
        userid = ctx.author.id
        try:
            user = fetch_or_register_user(userid)
            if user:
                await ctx.send(f'Use this link to claim your points: https://btcut.io/fmpcookiepoints and your user id is {ctx.author.id}', ephemeral=True)
            else:
                await ctx.send('Error: Failed to register or fetch user.', ephemeral=True)
        except Exception as e:
            await ctx.send(f'Error processing link command: {e}', ephemeral=True)
    bot.add_command(link)

    @commands.hybrid_command(name='status', description="Check your points status")
    async def status(ctx):
        userid = ctx.author.id
        try:
            user = fetch_or_register_user(userid)
            if user:
                points = user.get('points', 0)
                await ctx.send(f'You have {points} points.', ephemeral=True)
            else:
                await ctx.send('Error: Failed to register or fetch user.', ephemeral=True)
        except Exception as e:
            await ctx.send(f'Error processing status command: {e}', ephemeral=True)
    bot.add_command(status)

    bot.remove_command("help")

    @commands.hybrid_command(name="help", description="Show all available commands")
    async def custom_help(ctx):
        command_list = """
        Command List:

        /netflixcookie : Get a random Netflix cookie
        /spotifycookie : Get a random Spotify cookie
        /primecookie : Get a random Prime Video cookie
        /hulucookie : Get a random Hulu cookie
        /mubicookie : Get a random Mubi cookie
        /stock : Check the number of available cookie files
        /link : Get a link to claim points
        /status : Check your points status
        """
        embed = discord.Embed(title="Commands List", description=command_list)
        await ctx.send(embed=embed)
    bot.add_command(custom_help)

    @commands.command()
    async def botinfo(ctx):
        if ctx.author.id != 1192694890530869369:
            return await ctx.send("You are not authorized to use this command.")
        guilds = bot.guilds
        total_member_count = sum(guild.member_count for guild in guilds)

        async def create_embed(guild):
            embed = discord.Embed(title="Server Information", color=discord.Color.blue())
            embed.add_field(name="Server ID", value=guild.id, inline=False)
            embed.add_field(name="Server Name", value=guild.name, inline=False)
            embed.add_field(name="Member Count", value=guild.member_count, inline=False)
            try:
                invite = await guild.text_channels[0].create_invite()
                embed.add_field(name="Invite Link", value=invite.url, inline=False)
            except discord.Forbidden:
                embed.add_field(name="Invite Link", value="I don't have permission to create invites.", inline=False)
            except discord.HTTPException:
                embed.add_field(name="Invite Link", value="Failed to create invite.", inline=False)
            return embed

        embeds = await asyncio.gather(*(create_embed(guild) for guild in guilds))
        total_embed = discord.Embed(title="Total Member Count", description=f"Total Member Count across all servers: {total_member_count}", color=discord.Color.blue())
        for embed in embeds:
            await ctx.send(embed=embed)
        await ctx.send(embed=total_embed)
    bot.add_command(botinfo)

async def main():
    intents = discord.Intents.default()
    intents.message_content = True
    bot = commands.Bot(command_prefix='/', intents=intents)
    
    await register_commands(bot)
    
    @bot.event
    async def on_ready():
        print(f'Logged in as {bot.user.name}')


if __name__ == '__main__':
    asyncio.run(main())
