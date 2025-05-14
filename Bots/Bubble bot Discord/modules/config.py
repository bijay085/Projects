import json

CONFIG_FILE = 'config.json'

def load_config():
    with open(CONFIG_FILE, 'r') as config_file:
        return json.load(config_file)

def save_config(config):
    with open(CONFIG_FILE, 'w') as config_file:
        json.dump(config, config_file, indent=4)

config = load_config()
TOKEN = config.get('token')
BOT_OWNER_ID = config.get('bot_owner_id')

def get_server_config(guild_id):
    return config['servers'].get(str(guild_id))
