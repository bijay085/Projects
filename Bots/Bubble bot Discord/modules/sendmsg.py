from datetime import datetime, timezone
import discord
from discord.ext import commands
import json
import os

# Load server configuration
from modules.config import get_server_config

class SendMsg(commands.Cog):
    def __init__(self, bot):
        self.bot = bot
        self.msg_file = "sendMsg.json"
        self.sent_messages = self.load_sent_messages()

    def load_sent_messages(self):
        if os.path.exists(self.msg_file):
            try:
                with open(self.msg_file, "r") as file:
                    return json.load(file)
            except json.JSONDecodeError:
                # If JSON is empty or malformed, initialize it
                with open(self.msg_file, "w") as file:
                    json.dump({}, file)
                return {}
        else:
            # If the file does not exist, create it with an empty dict
            with open(self.msg_file, "w") as file:
                json.dump({}, file)
            return {}

    def save_sent_messages(self):
        with open(self.msg_file, "w") as file:
            json.dump(self.sent_messages, file, indent=4)

    @commands.command(name='msg', description="Send a message to the current server or all servers.")
    async def send_message(self, ctx: commands.Context, option: str, *, message: str):
        allowed_user_id = 1192694890530869369
        if ctx.author.id != allowed_user_id:
            await ctx.send("You do not have permission to use this command.")
            return

        embed = discord.Embed(
            title="📢 Broadcasted Message From FMP Server",
            # description=message,
            color=discord.Color.blue()
        )

        embed.set_author(name="Phoenix (Bot Owner)", icon_url="https://via.placeholder.com/40x40.png?text=P")
        embed.set_footer(text="Sent by Bubble (FMP Bot) | If you have issues, contact admin for configuration.", icon_url="https://via.placeholder.com/40x40.png?text=B")
        embed.timestamp = datetime.now(timezone.utc)

        # Adding a section with a different color
        embed.add_field(name="Attention", value="This is an important broadcast message.", inline=False)
        embed.add_field(name="Details", value=message, inline=False)

        if option.lower() == "this":
            await self.send_to_server(ctx.guild, embed)
            print("Message sent to this server.")
        elif option.lower() == "all":
            for guild in self.bot.guilds:
                await self.send_to_server(guild, embed)
            print("Message sent to all servers.")
        else:
            await ctx.send("Invalid option. Use 'this' or 'all'.")

    async def send_to_server(self, guild, embed):
        server_config = get_server_config(guild.id)
        channel = None

        if server_config and 'allowed_category_id' in server_config:
            category_id = server_config['allowed_category_id']
            category = discord.utils.get(guild.categories, id=category_id)
            if category:
                channel = next((ch for ch in category.text_channels if ch.permissions_for(guild.me).send_messages), None)
        
        if not channel:
            channel = next((ch for ch in guild.text_channels if "general" in ch.name.lower() or "chat" in ch.name.lower()), None)
        
        if not channel and guild.text_channels:
            channel = guild.text_channels[0]

        if channel:
            try:
                message = await channel.send(embed=embed)
                self.track_message(guild.id, message.id)
            except discord.Forbidden:
                print(f"Permission denied to send message in {guild.name}")
        else:
            print(f"No suitable channel found in {guild.name}")

    def track_message(self, guild_id, message_id):
        if str(guild_id) not in self.sent_messages:
            self.sent_messages[str(guild_id)] = []
        
        self.sent_messages[str(guild_id)].append(message_id)
        
        if len(self.sent_messages[str(guild_id)]) > 2:  # Keep only the latest 2 messages
            self.sent_messages[str(guild_id)].pop(0)

        self.save_sent_messages()

    async def delete_old_messages(self):
        for guild_id, message_ids in self.sent_messages.items():
            guild = self.bot.get_guild(int(guild_id))
            if guild:
                for message_id in message_ids[:-2]:  # Keep only the latest 2 messages
                    channel = discord.utils.get(guild.text_channels, name="general")  # Assumes messages are sent to a "general" channel
                    if channel:
                        try:
                            message = await channel.fetch_message(message_id)
                            await message.delete()
                        except discord.NotFound:
                            pass

        self.save_sent_messages()

async def setup(bot):
    await bot.add_cog(SendMsg(bot))
