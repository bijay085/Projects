import discord
from discord.ext import commands
from bot import MyBot
# from botMain import MyBot

class Insight(commands.Cog):
    def __init__(self, bot: commands.Bot):
        self.bot = bot
        self.private_channel_id = 1213698268979265607
        self.private_channel = self.bot.get_channel(self.private_channel_id)

    @commands.Cog.listener()
    async def on_member_join(self, member):
        """Sends a welcome message to the private channel when a new member joins."""
        if self.private_channel:
            await self.private_channel.send(f"A new member has joined: {member.display_name}")

    @commands.Cog.listener()
    async def on_member_remove(self, member):
        """Sends a farewell message to the private channel when a member leaves."""
        if self.private_channel:
            await self.private_channel.send(f"A member has left: {member.display_name}")

# most imp for load functions
async def setup(bot: commands.Bot):
    await bot.add_cog(Insight(bot))