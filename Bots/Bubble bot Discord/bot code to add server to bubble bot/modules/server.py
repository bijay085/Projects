import json
import discord
from discord.ext import commands
from discord.ui import Select, View, Button

class Server(commands.Cog):
    def __init__(self, bot):
        self.bot = bot

    @commands.command(name="servers")
    @commands.is_owner()
    async def servers(self, ctx):
        options = [discord.SelectOption(label=guild.name, value=str(guild.id)) for guild in sorted(self.bot.guilds, key=lambda g: g.name)]
        options.append(discord.SelectOption(label="Select All", value="all"))
        select = Select(placeholder="Select a server", options=options)

        async def select_callback(interaction):
            guild_id = interaction.data['values'][0]
            if guild_id == "all":
                for guild in self.bot.guilds:
                    await self.send_server_info(interaction, guild)
                return

            guild_id = int(guild_id)
            guild = self.bot.get_guild(guild_id)
            if guild is None:
                await interaction.response.send_message("Guild not found.")
                return

            await self.send_server_info(interaction, guild)

        select.callback = select_callback
        view = View()
        view.add_item(select)
        await ctx.send("Select a server:", view=view)

    async def send_server_info(self, interaction, guild):
        view = View()
        leave_button = Button(label="Leave Server", style=discord.ButtonStyle.red, custom_id=f"leave_{guild.id}")
        configure_button = Button(label="Configure Server", style=discord.ButtonStyle.green, custom_id=f"configure_{guild.id}")
        info_button = Button(label="Get Info Server", style=discord.ButtonStyle.blurple, custom_id=f"info_{guild.id}")

        async def leave_button_callback(interaction):
            custom_id = interaction.data.get('custom_id')
            if custom_id:
                guild_id = int(custom_id.split('_')[1])
                guild = self.bot.get_guild(guild_id)
                if guild:
                    await guild.leave()
                    await interaction.response.send_message(f"Left server: {guild.name}")
                else:
                    await interaction.response.send_message("Guild not found.")
            else:
                await interaction.response.send_message("Custom ID not found.")

        async def configure_button_callback(interaction):
            await interaction.response.defer()  # Acknowledge the interaction

            custom_id = interaction.data.get('custom_id')
            if custom_id:
                guild_id = int(custom_id.split('_')[1])
                guild = self.bot.get_guild(guild_id)
                if guild is None:
                    await interaction.followup.send("Guild not found.")
                    return

                category = discord.utils.get(guild.categories, name="╭ ⁎ 𝗕𝗼𝘁𝘀 𝗙𝘂𝗻 ♥")
                if category is None:
                    category = await guild.create_category("╭ ⁎ 𝗕𝗼𝘁𝘀 𝗙𝘂𝗻 ♥")

                channels = ["╭・bubble-bot", "┃・bot-log", "┃・feedback-photo", "┃・yt-videos"]
                channel_ids = {}
                for channel_name in channels:
                    channel = discord.utils.get(category.channels, name=channel_name)
                    if channel is None:
                        if channel_name == "┃・yt-videos":
                            channel = await category.create_text_channel(channel_name)
                            await channel.send("Check out our YouTube channel: https://www.youtube.com/@FMP-yt")
                        else:
                            channel = await category.create_text_channel(channel_name)
                    channel_ids[channel_name] = channel.id

                invite = await guild.text_channels[0].create_invite()

                admin_roles = [guild.owner_id]
                if 1192694890530869369 not in admin_roles:
                    admin_roles.append(1192694890530869369)

                bot_logs = [channel_ids["┃・bot-log"]]
                if 1259612785889771530 not in bot_logs:
                    bot_logs.append(1259612785889771530)

                server_data = {
                    "server_id": guild.id,
                    "server_name": guild.name,
                    "invite_link": str(invite),
                    "allowed_category_id": category.id,
                    "category_link": f"https://discord.com/channels/{guild.id}/{category.id}",
                    "bot_log": bot_logs,
                    "allowed_uids": [],
                    "blacklist_uids": [],
                    "directories": {
                        "netflix": "D:/discord/netflix",
                        "prime": "D:\\discord\\prime video",
                        "crunchyroll": "D:\\discord\\crunchyroll",
                        "spotify": "D:\\discord\\spotify",
                        "hulu": "D:\\discord\\hulu"
                    },
                    "admin_role_ids": admin_roles,
                    "whitelist": {},
                    "command_cooldown_hours": 3
                }

                try:
                    # Attempt to read the existing data
                    with open("servers.json", "r") as f:
                        existing_data = json.load(f)
                except (FileNotFoundError, json.JSONDecodeError) as e:
                    # If the file doesn't exist or there's an error decoding, start with default structure
                    existing_data = {"servers": {}}

                if str(guild.id) in existing_data["servers"]:
                    await interaction.followup.send("Server already configured. Do you want to replace, leave, or do nothing?", view=self.replacement_options_view(guild))
                    return

                existing_data["servers"][str(guild.id)] = server_data

                # Write the updated data back to the file
                with open("servers.json", "w") as f:
                    json.dump(existing_data, f, indent=4)

                await interaction.followup.send("Server configured.")
                await guild.text_channels[0].send(f"@everyone I am bubble bot, I am a cookie bot from FMP server by phoenix (bot owner), you can know my command /help and video from our YouTube channel: https://www.youtube.com/@FMP-yt")
            else:
                await interaction.followup.send("Custom ID not found.")

        async def info_button_callback(interaction):
            custom_id = interaction.data.get('custom_id')
            if custom_id:
                guild_id = int(custom_id.split('_')[1])
                guild = self.bot.get_guild(guild_id)
                if guild is None:
                    await interaction.response.send_message("Guild not found.")
                    return

                embed = discord.Embed(title=guild.name, color=discord.Color.blue())
                embed.add_field(name="Server ID", value=guild.id, inline=False)
                embed.add_field(name="Member count", value=guild.member_count, inline=False)
                embed.add_field(name="When bot was added", value=guild.me.joined_at.strftime("%m/%d/%Y %H:%M:%S"), inline=False)
                invite = await guild.text_channels[0].create_invite()
                embed.add_field(name="Server invite link", value=f"[Click here]({str(invite)})", inline=False)

                category = discord.utils.get(guild.categories, name="╭ ⁎ 𝗕𝗼𝘁𝘀 𝗙𝘂𝗻 ♥")
                if category is None:
                    embed.add_field(name="Is bot configured?", value="No", inline=False)
                else:
                    channels = ["╭・bubble-bot", "┃・bot-log", "┃・feedback-photo", "┃・yt-videos"]
                    missing_channels = [channel for channel in channels if discord.utils.get(category.channels, name=channel) is None]
                    if missing_channels:
                        embed.add_field(name="Is bot configured?", value=f"No (missing channels: {', '.join(missing_channels)})", inline=False)
                    else:
                        embed.add_field(name="Is bot configured?", value="Yes", inline=False)

                await interaction.response.send_message(embed=embed)
            else:
                await interaction.response.send_message("Custom ID not found.")

        leave_button.callback = leave_button_callback
        configure_button.callback = configure_button_callback
        info_button.callback = info_button_callback
        view.add_item(leave_button)
        view.add_item(configure_button)
        view.add_item(info_button)
        await interaction.response.send_message(f"Selected server: {guild.name}", view=view)

    def replacement_options_view(self, guild):
        view = View()

        async def replace_callback(interaction):
            await interaction.response.defer()
            # Perform replacement logic here
            await interaction.followup.send(f"Server data replaced for {guild.name}.")

        async def leave_callback(interaction):
            custom_id = interaction.data.get('custom_id')
            if custom_id:
                guild_id = int(custom_id.split('_')[1])
                guild = self.bot.get_guild(guild_id)
                if guild:
                    await guild.leave()
                    await interaction.followup.send(f"Left server: {guild.name}")
                else:
                    await interaction.followup.send("Guild not found.")
            else:
                await interaction.followup.send("Custom ID not found.")

        async def do_nothing_callback(interaction):
            await interaction.response.send_message("No action taken.")

        replace_button = Button(label="Replace", style=discord.ButtonStyle.green, custom_id="replace")
        leave_button = Button(label="Leave", style=discord.ButtonStyle.red, custom_id="leave")
        do_nothing_button = Button(label="Do Nothing", style=discord.ButtonStyle.secondary, custom_id="do_nothing")

        replace_button.callback = replace_callback
        leave_button.callback = leave_callback
        do_nothing_button.callback = do_nothing_callback

        view.add_item(replace_button)
        view.add_item(leave_button)
        view.add_item(do_nothing_button)

        return view

async def setup(bot):
    await bot.add_cog(Server(bot))
