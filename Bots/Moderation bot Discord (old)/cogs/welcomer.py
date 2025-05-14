import discord
from discord.ext import commands
from discord import app_commands
from bot import MyBot
import json

#code/cog for making welcomer
class Welcomer(commands.Cog):
    def __init__(self, bot: MyBot):
        self.bot = bot
    
    @commands.Cog.listener()
    async def on_member_join(self, member:discord.Member):
        if member.bot:
            return
        
        with open("./data.json", "r") as f:
            records = json.load(f)
            
        try:
            channel_id = records[str(member.guild.id)]
        except KeyError:
            return
        
        channel = self.bot.get_channel(int(channel_id))
        if not channel:
            return
        
        try:
            await channel.send(f"Welcome {member.mention} ❣️!This a new channel so there are very less member. Hope you understand but you will find most of the thing you need. You are {member.guild.member_count}th of {member.guild.name}")
        except discord.Forbidden:
            guild = member.guild
            owner = guild.owner
            
            await owner.send("Give me permission please .😁")
    
    @app_commands.command()
    #to  add a welcome message to the server, he/she must be Owner or admin or mod 
    @app_commands.checks.has_role(1212384304068427854 or 1212384304068427853 or 1212389284523278396 ) 
    async def welcome(self, interaction:discord.Interaction):
        with open("./data.json", "r") as f:
            records = json.load(f)
            
        records[str(interaction.guild_id)] = str(interaction.channel_id)
        with open("./data.json", "w") as f:
            json.dump(records, f)
            await interaction.response.send_message(f"Success ! {interaction.channel.mention} is your welcome channel .")

        
async def setup(bot: commands.Bot):
    await bot.add_cog(Welcomer(bot))

