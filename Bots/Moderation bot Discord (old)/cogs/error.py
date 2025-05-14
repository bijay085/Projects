import discord
from discord.ext import commands
from discord import app_commands
from bot import MyBot


#code/cog for making error handling 
class ErrorCog(commands.Cog):
    def __init__(self, bot: MyBot):
        self.bot = bot
        bot.tree.on_error = self.on_app_command_error

    async def on_app_command_error(self, interaction: discord.Interaction, error: app_commands.AppCommandError):
        if isinstance(error, app_commands.MissingRole):
            role = interaction.guild.get_role(error.missing_role)
            if not role:
                return  "Error: The specified role does not exist."

            await interaction.respond.send_message(f"Missing Role Error . {role.name} !")


    @commands.Cog.listener()
    async def on_command_error(self, ctx:commands.Context, error:commands.CommandError):
        if isinstance(error, commands.MissingRequiredArgument):
            return await ctx.send(f"Missing required agrument - {error.param.name}")
        elif isinstance(error, commands.CommandNotFound):
            return await ctx.send("Command not found")
        elif isinstance(error, commands.MissingPermissions):
            perms = ""
            for p in error.missing_permissions:
                perms += f"{p},"
            return await ctx.send(f"You need {perms} to use this command.") 
        else:
            raise error
        
        
async def setup(bot: commands.Bot):
    await bot.add_cog(ErrorCog(bot))

