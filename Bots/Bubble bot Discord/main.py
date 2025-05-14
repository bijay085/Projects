from datetime import datetime
import discord
from discord.ext import commands, tasks
import asyncio
from modules.config import TOKEN, load_config, get_server_config
from modules.database import fetch_or_register_user
from modules.decorators import check_admin, check_blacklist, check_category
from modules.commands import register_commands
import traceback
import random

# Define intents
intents = discord.Intents.all()

# Create bot instance with intents
bot = commands.Bot(command_prefix="!", intents=intents)

# Store bot start time
bot.start_time = None

# Define status messages and notes with emojis
status_messages = [
    "🍪 Playing with cookies",
    "👂 Listening for cookie orders",
    "👀 Watching the cookie jar",
    "🚚 Delivering cookies",
    "🍞 Baking fresh cookies"
]

notes = [
    "💧 Remember to stay hydrated!",
    "🆕 Check out the latest features!",
    "🚨 Don't forget to follow the rules!",
    "🔔 Keep an eye on the updates!",
    "🙏 Thank you for your support!"
]

@tasks.loop(minutes=10)
async def change_status():
    # Alternate between status messages and notes
    current_time = datetime.now().strftime("%I:%M:%S %p")
    
    # Choose a random status or note depending on the loop iteration
    if change_status.current_loop % 2 == 0:
        status = random.choice(status_messages)
        status_with_time = f"{status} | {current_time}"
        await bot.change_presence(activity=discord.Game(name=status_with_time))
    else:
        note = random.choice(notes)
        note_with_time = f"{note} | {current_time}"
        await bot.change_presence(activity=discord.Game(name=note_with_time))

@bot.event
async def on_ready():
    bot.start_time = datetime.now()  # Set the start time when the bot is ready
    print("Bot is ready and connected")
    
    # Start the status change loop
    change_status.start()
    
    try:
        synced = await bot.tree.sync()
        print(f"Synced {len(synced)} command(s)")
    except Exception as e:
        print(f"Error syncing commands: {e}")

    try:
        command_count = len(bot.tree.get_commands())
        print(f"Bot loaded with {command_count} commands.")
    except Exception as e:
        print(f"Error counting loaded commands: {e}")

@bot.event
async def on_message(message):
    if bot.user.mentioned_in(message):
        current_time = datetime.now()
        uptime = current_time - bot.start_time
        days, seconds = uptime.days, uptime.seconds
        hours = seconds // 3600
        minutes = (seconds % 3600) // 60
        uptime_str = f"{days}d {hours}h {minutes}m"
        await message.channel.send(f"👋 Hello, I have been online for {uptime_str}.")
    await bot.process_commands(message)

@bot.event
async def on_error(event, *args, **kwargs):
    traceback.print_exc()
    print(f"An error occurred in event: {event}, Args: {args}, Kwargs: {kwargs}")

    # Safely attempt to get server config
    try:
        if args and hasattr(args[0], 'guild') and args[0].guild:
            server_config = get_server_config(args[0].guild.id)
            log_channel = bot.get_channel(server_config['bot_log'])
            if log_channel:
                await log_channel.send(f"⚠️ Error occurred in event {event}: {args[0]}")
    except Exception as e:
        print(f"Error handling on_error: {e}")

# Register all commands and run the bot
async def main():
    await register_commands(bot)
    await bot.start(TOKEN)

asyncio.run(main())
