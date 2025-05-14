from functools import wraps
import discord
from modules.config import get_server_config

BOT_OWNER_PROFILE_LINK = "https://discord.gg/flame-mod-paradise-1212384304068427846"

def check_server_config(func):
    @wraps(func)
    async def wrapper(ctx, *args, **kwargs):
        interaction = ctx.interaction if hasattr(ctx, 'interaction') else ctx
        guild = interaction.guild if hasattr(interaction, 'guild') else ctx.guild
        if not guild:
            await ctx.send("This command cannot be used in direct messages.", ephemeral=True)
            return
        server_config = get_server_config(guild.id)
        if not server_config:
            await ctx.send(f"Your server is not configured by the bot owner. If you want to configure, please contact the bot owner [here]({BOT_OWNER_PROFILE_LINK}).", ephemeral=True)
            return
        return await func(ctx, *args, **kwargs)
    return wrapper

def check_admin():
    def decorator(func):
        @wraps(func)
        @check_server_config
        async def wrapper(ctx, *args, **kwargs):
            interaction = ctx.interaction if hasattr(ctx, 'interaction') else ctx
            guild = interaction.guild if hasattr(interaction, 'guild') else ctx.guild
            user = interaction.user if hasattr(interaction, 'user') else ctx.author
            server_config = get_server_config(guild.id)
            if is_administrator(user, server_config):
                return await func(ctx, *args, **kwargs)
            await ctx.send("You do not have permission to use this command.", ephemeral=True)
        return wrapper
    return decorator

def check_blacklist():
    def decorator(func):
        @wraps(func)
        @check_server_config
        async def wrapper(ctx, *args, **kwargs):
            interaction = ctx.interaction if hasattr(ctx, 'interaction') else ctx
            guild = interaction.guild if hasattr(interaction, 'guild') else ctx.guild
            user = interaction.user if hasattr(interaction, 'user') else ctx.author
            server_config = get_server_config(guild.id)
            if user.id in server_config['blacklist_uids']:
                await ctx.send("You are blacklisted for safety purposes.", ephemeral=True)
                return
            return await func(ctx, *args, **kwargs)
        return wrapper
    return decorator

def check_category():
    def decorator(func):
        @wraps(func)
        @check_server_config
        async def wrapper(ctx, *args, **kwargs):
            interaction = ctx.interaction if hasattr(ctx, 'interaction') else ctx
            guild = interaction.guild if hasattr(interaction, 'guild') else ctx.guild
            channel = interaction.channel if hasattr(interaction, 'channel') else ctx.channel
            server_config = get_server_config(guild.id)
            if channel.category_id != server_config['allowed_category_id']:
                await ctx.send(f"This command can only be used in the specific category. Please use it [here]({server_config['category_link']}).", ephemeral=True)
                return
            return await func(ctx, *args, **kwargs)
        return wrapper
    return decorator

def is_administrator(user: discord.Member, server_config) -> bool:
    admin_role_ids = server_config.get('admin_role_ids', [])
    return any(role.id in admin_role_ids for role in user.roles)
