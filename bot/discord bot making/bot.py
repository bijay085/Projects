import discord
from discord.ext import commands
#importing config.py another file for discord configuration files
import config

# intents and bot variables are created 
# discord dev portal have 3 intents they must be on too
intents = discord.Intents.all()
bot = commands.Bot(command_prefix="[", intents=intents)

# @bot.event == event listener 
@bot.event
async def on_ready():
    print("Bot is ready !")  
#when bot got connected to discord and came online , it will say this in vs terminal

#command made
@bot.command()  
async def ping(ctx): 
#ping function is made, ctx = context, it is a variable
    await ctx.send("Hy !")
#.send is a method, to send info to channel

#*********When [ping is written in discord channel it will written Hy !******************

#bot is run, with discord token from another config.file 
bot.run(config.DISCORD_TOKEN)