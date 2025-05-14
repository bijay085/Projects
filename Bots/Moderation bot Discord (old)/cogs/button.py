import discord
from discord.ext import commands
from discord.ui import View as view, Button
from config import DISCORD_TOKEN

class InviteButtons(view):
    def __init__(self, inv: str):
        super().__init__()
        self.inv = inv
        self.add_item(discord.ui.Button(label="Invite link", url=self.inv))

    @discord.ui.button(label="Invite btn", style=discord.ButtonStyle.green)
    async def inviteBtn(self, interaction: discord.Interaction, button: discord.ui.Button):
        await interaction.response.send_message("sileo://source/https://pickyone.github.io/kaizer/", ephemeral=True)

class ButtonCog(commands.Cog):
    def __init__(self, bot: commands.Bot):
        self.bot = bot

    @commands.command()
    async def invite(self, ctx: commands.Context):
        inv = await ctx.channel.create_invite()
        await ctx.send("Invite links", view=InviteButtons(str(inv)))

async def setup(bot: commands.Bot):
    await bot.add_cog(ButtonCog(bot))