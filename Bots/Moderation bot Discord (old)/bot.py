import discord
from discord.ext import commands
import config
from datetime import datetime, timedelta, timezone
from discord.ui import View, Button
from discord.ui import View as view


class MyBot(commands.Bot):
    def __init__(self, command_prefix: str, intents=discord.Intents, **kwargs):
        super().__init__(command_prefix, intents=intents, **kwargs)

    async def on_ready(self):
        await bot.tree.sync()
        # We need to tell discord that we have this this function
        await bot.load_extension("cogs.mod")
        print("Moderation cog loaded")
        await bot.load_extension("cogs.insight")
        print("Insight cog loaded")
        await bot.load_extension("cogs.error")
        print("Error cog loaded")
        await bot.load_extension("cogs.welcomer")
        print("Welcomer cog loaded")
        await bot.load_extension("cogs.button")
        print("Button cog loaded")
        print("Bot is ready !")


#button making *************************----------------

if __name__ == "__main__":
    # Create an instance of the bot
    bot = MyBot(command_prefix="!", intents=discord.Intents.all())
    bot.run(config.DISCORD_TOKEN)


