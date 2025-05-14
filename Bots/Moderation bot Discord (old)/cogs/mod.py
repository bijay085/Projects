import discord
from discord.ext import commands
from datetime import datetime, timedelta, timezone
from discord import app_commands
from bot import MyBot
# from botMain import MyBot
class Mod(commands.Cog):
    def __init__(self, bot: MyBot):
        self.bot = bot

    @app_commands.command()
    @app_commands.checks.has_permissions(kick_members=True)
    @commands.bot_has_permissions(kick_members=True)
    async def kick(self, interaction: discord.Interaction, member: discord.Member, *, reason: str):
        """Kicks a mentioned user from the server."""
        await member.kick(reason=reason)
        await interaction.response.send_message(f"{member} has been kicked for `{reason}`.")

    @app_commands.command()
    @app_commands.checks.has_permissions(ban_members=True)
    @commands.bot_has_permissions(ban_members=True)
    async def ban(self, interaction: discord.Interaction, member: discord.Member, *, reason: str):
        """Bans a mentioned user from the server."""
        await member.ban(reason=reason)
        await interaction.response.send_message(f"{member} has been banned from this server. Reason: `{reason}`")

    @app_commands.command()
    @app_commands.checks.has_permissions(ban_members=True)
    async def warn(self, interaction: discord.Interaction, member: discord.Member, *, reason: str):
        """Warns a mentioned user."""
        await interaction.response.send_message(f"{member} has been warned for `{reason}`.")

    @app_commands.command()
    @app_commands.checks.has_permissions(ban_members=True)
    async def timeout(self, interaction: discord.Interaction, member: discord.Member, minutes: int, *, reason: str):
        """Times out a mentioned user for a specified number of minutes."""
        delta = timedelta(minutes=minutes)
        timeout_end = datetime.now(timezone.utc) + delta

        # Timeout the member (assuming a custom timeout system)
        await member.edit(timed_out_until=timeout_end)
        await interaction.response.send_message(f"{member} has been timed out for `{reason}` until {timeout_end}.")

    async def timeout_member(
        self, member: discord.Member, timeout_end: datetime, reason: str
    ):
        """Your custom timeout logic goes here."""
        # For example, you might want to store the timeout information in a database
        # Update your database or data structure accordingly

        # Then, you can use edit to update the member's timeout status
        await member.edit(
            timed_out_until=timeout_end,
            reason=f"{reason} - Timeout until {timeout_end}",
        )

    @app_commands.command()
    @app_commands.checks.has_permissions(ban_members=True)
    async def dm(self, interaction: discord.Interaction, member: discord.Member, *, message: str):
        """DMs a mentioned user."""
        await member.send(message)
        await interaction.response.send_message(f"Sent `{message}` to {member}.")
                
#most imp for load functions             
async def setup(bot: commands.Bot):
    await bot.add_cog(Mod(bot))

