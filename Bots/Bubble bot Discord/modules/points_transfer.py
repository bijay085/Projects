import discord
from discord.ext import commands
from discord import app_commands
from modules.database import fetch_or_register_user, update_user_points
from modules.config import get_server_config, save_config, config
from modules.decorators import check_admin, check_blacklist, check_category
from datetime import datetime, timedelta
import logging
import motor.motor_asyncio
import os

# Set up logging
logger = logging.getLogger(__name__)

# Load MongoDB connection string from environment variables
MONGODB_CONNECTION_STRING = "mongodb+srv://Bijay:bijay%40123@cluster0.hpl6qfx.mongodb.net/db_discord?retryWrites=true&w=majority"
if not MONGODB_CONNECTION_STRING:
    logger.error("MongoDB connection string not found in environment variables.")

# Initialize MongoDB client
mongo_client = motor.motor_asyncio.AsyncIOMotorClient(MONGODB_CONNECTION_STRING)
db = mongo_client['db_discord']
collection = db['tbl_discord']
transfer_collection = db['tbl_transfers']  # New collection to track transfers

class PointsTransfer(commands.Cog):
    def __init__(self, bot):
        self.bot = bot

    @commands.hybrid_command(name='transfer', description='Transfer your points to another user')
    @check_blacklist()
    @check_category()
    async def transfer_points(self, ctx, points: int, user: discord.Member):
        if points <= 0:
            await ctx.send("Points to transfer must be greater than zero.", ephemeral=True)
            return

        sender_id = ctx.author.id
        receiver_id = user.id

        if sender_id == receiver_id:
            await ctx.send("You cannot transfer points to yourself.", ephemeral=True)
            return

        # Check transfer cooldown
        can_transfer = await self.can_transfer(sender_id)
        if not can_transfer:
            await ctx.send("You can transfer points only once every 24 hours.", ephemeral=True)
            return

        # Fetch sender and receiver data
        sender_data = await fetch_or_register_user(sender_id)
        receiver_data = await fetch_or_register_user(receiver_id)

        if not sender_data or not receiver_data:
            await ctx.send("Failed to fetch user data.", ephemeral=True)
            return

        sender_points = sender_data.get('points', 0)

        if sender_points < points:
            await ctx.send("You do not have enough points to transfer.", ephemeral=True)
            return

        # Update points
        await update_user_points(sender_id, -points)
        await update_user_points(receiver_id, points)

        # Record the transfer
        await transfer_collection.insert_one({
            'sender_id': sender_id,
            'receiver_id': receiver_id,
            'points': points,
            'timestamp': datetime.utcnow()
        })

        await ctx.send(f"You have successfully transferred {points} points to {user.mention}.", ephemeral=True)

    @commands.hybrid_command(name='give', description='[Admin] Give or deduct points from a user')
    @check_admin()
    async def give_points(self, ctx, points: int, user: discord.Member):
        receiver_id = user.id

        # Fetch receiver data
        receiver_data = await fetch_or_register_user(receiver_id)

        if not receiver_data:
            await ctx.send("Failed to fetch user data.", ephemeral=True)
            return

        # Update points
        await update_user_points(receiver_id, points)

        action = "added to" if points >= 0 else "deducted from"
        await ctx.send(f"{abs(points)} points have been {action} {user.mention}'s balance.", ephemeral=True)

    async def can_transfer(self, user_id: int) -> bool:
        # Check if the user has transferred in the last 24 hours
        time_threshold = datetime.utcnow() - timedelta(hours=24)
        recent_transfer = await transfer_collection.find_one({
            'sender_id': user_id,
            'timestamp': {'$gte': time_threshold}
        })
        return recent_transfer is None

async def setup(bot):
    await bot.add_cog(PointsTransfer(bot))
