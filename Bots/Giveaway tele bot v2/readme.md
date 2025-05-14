---

# Giveaway Bot - Admin Guide

Welcome to the Giveaway Bot! This guide is intended for the admin/owner/giveaway organizer. It will help you understand how to set up, run, and manage the bot for your giveaways.

## Introduction

The Giveaway Bot is designed to help you efficiently manage giveaways through Telegram. It handles participant entries, winner selection, blacklisting, disqualifications, and more—all through simple commands and interactions.

## Setup Instructions

### Prerequisites

- **Telegram Bot Token**: Obtain one by talking to [@BotFather](https://t.me/BotFather) on Telegram.
- **Admin IDs**: Your Telegram user ID and any other admins who will manage the bot.
- **Bot Files**: Ensure you have all the necessary bot files provided.

### Initial Setup

1. **Create a Configuration File**

   - Create a file named `.env` in the same directory as your bot files.
   - Inside the `.env` file, add the following lines:

     ```
     BOT_TOKEN=your_telegram_bot_token
     ADMIN_IDS=admin_id1,admin_id2
     ```

     - Replace `your_telegram_bot_token` with the token you received from BotFather.
     - Replace `admin_id1,admin_id2` with the Telegram user IDs of the admins (e.g., `123456789,987654321`).

2. **Configuring the Giveaway**

   - When you run the bot for the first time, it will prompt you to enter some configuration settings in the console.
   - These settings include:

     - **Link**: The link to the post where participants should leave feedback after receiving their reward.
     - **Ignore Time**: The amount of time (in seconds) after starting the giveaway during which entries are ignored (to prevent users from bypassing steps).
     - **Number of Winners**: The total number of winners for the giveaway.
     - **Number of Extra Winners**: Additional winners who can receive a reward if the main winners are disqualified or for any other reason.
     - **Giveaway Code**: The code that participants need to send to the bot to enter the giveaway.

   - These configurations are saved in a `config.json` file for future use.

3. **Prepare the Reward Files**

   - **Create a `cookies` Directory**: In the same folder as your bot files, create a directory named `cookies`.
   - **Add Reward Files**: Place all the reward files (e.g., `.txt` files) that you plan to send to the winners into the `cookies` directory.

4. **Start the Bot**

   - Run the bot script using your preferred method (e.g., double-clicking the script or running it from the command line).
   - The bot will start and begin listening for commands and user interactions.

## Running the Giveaway

### Starting a Giveaway

- **Command**: `/startg`
- **Usage**: Send this command in bot dm.
- **Notes**:
  - Only admins can start a giveaway.
  - When a giveaway is started:
    - The bot resets any previous giveaway data.
    - The `disqualify.txt`, `winner.txt`, and `extra winner.txt` files are cleared.
    - Participants can now enter the giveaway by sending the correct giveaway code to the bot in a private message.

### Stopping a Giveaway

- **Command**: `/stopg`
- **Usage**: Send this command in bot dm.
- **Notes**:
  - Only admins can stop a giveaway.
  - When a giveaway is stopped:
    - The bot generates a report containing the winners, extra winners, disqualified users, and blacklisted users.
    - The report is sent to all admins.
    - Temporary files used during the giveaway are deleted.

### Monitoring Giveaway Progress

- **Command**: `/countg`
- **Usage**: Send this command to check the status of the current giveaway.
- **Notes**:
  - The bot will inform you of:
    - The number of winners selected and remaining.
    - The number of extra winners selected and remaining.

### Fetching Updated Data

- **Command**: `/fetchg`
- **Usage**: Use this command after manually editing any of the files (e.g., `blacklist.txt`, `disqualify.txt`, etc.).
- **Notes**:
  - The bot will reload the in-memory data from the files.
  - It will report any changes detected.

### Sending Rewards to Winners

- **Command**: `/sendg`
- **Usage**: Send this command to distribute the reward files to the winners in bot dm.
- **Notes**:
  - The bot will send files from the `cookies` directory to the winners.
  - Ensure that the number of files in the `cookies` directory is sufficient for the number of winners.

## Admin Commands

### List of Commands

- `/startg`: Start a new giveaway (admin only, bot dm).
- `/stopg`: Stop the current giveaway (admin only, bot dm).
- `/countg`: Check the status of the current giveaway (admin only, bot dm).
- `/sendg`: Send the reward files to the winners (admin only, bot dm).
- `/fetchg`: Reload data from files after manual edits (admin only, bot dm).
- `/blacklist @username or user_id`: Blacklist a user from all giveaways (admin only, channel).
- `/disqualify @username or user_id`: Disqualify a user from the current giveaway(admin only, channel).
- `/removeb @username or user_id`: Remove a user from the blacklist(admin only, channel).
- `/removed @username or user_id`: Remove a user from the disqualified list(admin only, channel).
- `/getinfo @username or user_id`: Get information about a user's status.
- `/help`: Display help information (bot dm).

### Using Admin Commands

- **General Usage**:
  - Admin commands can be used in any chat where the bot is present.
  - Replace `@username` with the user's Telegram username or `user_id` with their Telegram user ID.

- **Commands Requiring Confirmation**:
  - For commands that affect a user's status (`/blacklist`, `/disqualify`, `/removeb`, `/removed`), the bot will request confirmation from all admins via direct message.
  - Another admin must confirm the action by replying with 'yes' or 'no' in their private chat with the bot.
  - Once confirmed, the action is carried out, and a notification is sent to the group where the command was issued.

#### Example: Blacklisting a User

1. **Initiate the Command**:
   - Send `/blacklist @username` or `/blacklist user_id` in the chat.
2. **Confirmation Request**:
   - The bot will notify all admins for confirmation.
3. **Admin Confirmation**:
   - An admin confirms by replying 'yes' in their private chat with the bot.
4. **Action Execution**:
   - The user is blacklisted.
   - A notification is sent to the group and to the user.

### Important Notes on Admin Commands

- **Ensure Correct User Identification**:
  - Double-check the username or user ID to avoid affecting the wrong user.
- **Admin Cooperation**:
  - Actions require at least one other admin's confirmation to prevent misuse.
- **Communication**:
  - Communicate with other admins if necessary to expedite confirmations.

## Participant Interaction

- **Joining the Giveaway**:
  - Participants join the giveaway by sending the giveaway code to the bot in a private message.
  - The code should be the exact one specified during the configuration.

- **Bot Responses**:
  - **Successful Entry**:
    - If the participant sends the correct code after the ignore time has passed, they will be entered into the giveaway.
    - They will receive a confirmation message.
  - **Early Entry Attempt**:
    - If they send the code before the ignore time has elapsed, they may be blacklisted for bypassing required steps.
  - **Incorrect Code**:
    - If they send the wrong code, they will be prompted to try again.
    - Multiple incorrect attempts can lead to disqualification.
  - **Spamming**:
    - Excessive messages in a short period can lead to disqualification or blacklisting.
  - **Already Participated**:
    - If they have already participated, they will be informed.
  - **Blacklisted or Disqualified Users**:
    - Users who are blacklisted or disqualified will be notified and prevented from participating.

## Files and Their Purposes

- **`config.json`**:
  - Stores the configuration settings for the giveaway.
  - Contains the link, ignore time, number of winners, extra winners, and giveaway code.

- **`blacklist.txt`**:
  - Contains the list of users who are blacklisted from all giveaways.
  - Each entry includes the date, username, user ID, and reason.

- **`disqualify.txt`**:
  - Contains the list of users disqualified from the current giveaway.
  - Each entry includes the date, username, user ID, and reason.

- **`winner.txt`**:
  - Records the winners of the current giveaway.
  - Each entry includes the date, position, username, and user ID.

- **`extra winner.txt`**:
  - Records the extra winners of the current giveaway.
  - Each entry includes the date, position, username, and user ID.

- **`cookies` Directory**:
  - Contains the reward files to be sent to the winners.
  - Ensure this directory exists and is populated before sending rewards.

- **Report Files**:
  - When a giveaway is stopped

, a report file is generated (e.g., `20231015_report.txt`).
  - The report summarizes the giveaway's outcome, including winners, extra winners, disqualified users, and blacklisted users.

## Important Notes

- **Admin IDs**:
  - Ensure that the admin IDs are correctly set in the `.env` file to have proper control over the bot.
  - Admin IDs should be the numerical Telegram user IDs.

- **Bot Permissions**:
  - The bot must have permission to read and send messages in the chats where it is used.
  - For group chats, make sure the bot is added and given the necessary rights.

- **Participant Instructions**:
  - Clearly inform participants about how to join the giveaway and any rules they need to follow.
  - Provide them with the link to the bot and the giveaway code.

- **Data Backup**:
  - Regularly back up important files, such as `blacklist.txt` and `config.json`, in case you need to restore them.

- **Spam Prevention**:
  - The bot has mechanisms to prevent spamming and misuse.
  - Ensure participants are aware to avoid being disqualified.

- **File Management**:
  - Keep the `cookies` directory updated with the correct reward files.
  - Ensure that there are enough files for the number of winners.

## Troubleshooting

- **Bot Not Responding**:
  - Check if the bot is running and connected to the internet.
  - Ensure that the bot token in the `.env` file is correct.

- **Participants Unable to Join**:
  - Verify that the giveaway is active.
  - Ensure that participants are sending the correct giveaway code.

- **Commands Not Working**:
  - Ensure that you're an admin.
  - Check that you're using the correct command syntax.
  - Confirm that the bot is present in the chat where you're issuing commands.

- **File Errors**:
  - Ensure all necessary files and directories exist and have the correct permissions.
  - Check for typos or incorrect file paths.

- **Confirmation Issues**:
  - If actions are not being confirmed, communicate with other admins.
  - Ensure that at least one other admin is available to confirm actions.

## FAQ

### 1. **What happens if the number of reward files is less than the number of winners?**

If the number of files in the `cookies` directory is less than the number of winners, the bot will distribute the available files by reusing the files until all winners receive one. This means the files will be cycled through for each winner. Make sure to have enough distinct reward files for a unique experience.

### 2. **Can participants be disqualified for sending the wrong code multiple times?**

Yes. If participants send the wrong giveaway code more than three times, they will automatically be disqualified from the current giveaway and receive a notification.

### 3. **What happens if participants try to enter before the ignore time is over?**

Participants who send the correct giveaway code before the ignore time has elapsed will be blacklisted for bypassing the necessary steps, such as shortlink verification. They will be prevented from participating in any future giveaways.

### 4. **Can the bot handle spamming attempts?**

Yes, the bot tracks user activity, and users who send too many messages in a short period will be warned. Repeated spam attempts will result in the user being disqualified from the current giveaway or even blacklisted from all future giveaways.

### 5. **What if an admin mistakenly disqualifies or blacklists a user?**

Admins can use the `/removeb` and `/removed` commands to remove users from the blacklist or disqualified list, respectively. Admin actions must be confirmed by another admin to ensure accountability.

---

By following this guide, you should now be able to manage your giveaways using the bot effectively!