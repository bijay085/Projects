import discord
from discord.ext import commands
import config
from datetime import datetime, timedelta, timezone

intents = discord.Intents.all()
bot = commands.Bot(command_prefix="!", intents=intents)

@bot.event
async def on_ready():
    await bot.load_extension("cogs.mod")
    print("mod clog loaded !")
    await bot.load_extension("cogs.insight")
    print("Insight clog loaded !")
    await bot.tree.sync()
    print("Bot is ready !")

@bot.event
async def on_message(msg: discord.Message):
    content = msg.content

    if msg.channel.id != 1213091115247468554:
        print("Wrong channel")
        return

    if content == "hello":
        await msg.reply("hi")
    await bot.process_commands(msg)

@bot.command()
async def ping(ctx):
    await ctx.send("Hy !")


# now making / slash command made
@bot.tree.command()
async def repo(interaction: discord.Interaction):
    """Gives you flame repo link."""
    await interaction.response.send_message(
        "https://pickyone.github.io/kaizer", ephemeral=True
    )
# now making / slash command made
@bot.tree.command()
async def invite(interaction: discord.Interaction):
    """Gives you this server link."""
    await interaction.response.send_message(
        "https://discord.gg/XAk9MH5HYb", ephemeral=True
    )

bot.run(config.DISCORD_TOKEN)