import json

def load_config():
    with open('config.json', 'r') as config_file:
        return json.load(config_file)

config = load_config()
TOKEN = config['token']

def get_server_config(guild_id):
    return config['servers'].get(str(guild_id))

def save_config():
    with open('config.json', 'w') as config_file:
        json.dump(config, config_file, indent=4)
