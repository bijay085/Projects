# 🤖 Bot Collection: Telegram & Discord

A comprehensive set of custom bots developed for Telegram and Discord, designed to automate tasks, enhance user interaction, and integrate with external systems. These projects represent various phases of development and real-world bot deployment experience.

---

## 📦 Included Bots

### 1. 🎈 Bubble Bot (Discord)
A feature-rich Discord bot focused on file rewards using a point system, backed by MongoDB and a Flask site.

**Features:**
- 🎯 Points tracking stored in MongoDB
- 💬 Slash commands: `/balance`, `/redeem`, `/earn`, `/help`
- 📤 File delivery based on point redemption
- 🌐 Flask web interface for users to complete tasks and earn points
- 🛡️ Admin tools for manual point adjustment and file sending

> Frameworks: `discord.py`, `Flask`, `pymongo`

---

### 2. 🎁 Giveaway Telegram Bot v1
A basic bot prototype to run Telegram giveaways.

**Features:**
- `/join` to participate
- Random winner selection
- Manual logging of users

> Framework: `pyTelegramBotAPI`

---

### 3. 🎁 Giveaway Telegram Bot v2
An improved version of the v1 bot with more advanced controls.

**Features:**
- Time-based giveaways
- Duplicate entry prevention
- Admin commands panel
- (Optional) Multi-language support

> Framework: `Telethon` or `aiogram`

---

### 4. 🛡️ Moderation Bot (Discord)
A legacy Discord bot used for basic server moderation.

**Features:**
- Auto-kick/ban for flagged actions
- Keyword filter
- Role permissions and logging

> Framework: `discord.py` (deprecated)

---

## 🛠 Technologies Used

- **Languages:** Python
- **Discord Framework:** `discord.py`
- **Telegram Frameworks:** `pyTelegramBotAPI`, `Telethon`, `aiogram`
- **Database:** MongoDB (`pymongo`)
- **Web Backend:** Flask
- **Hosting Options:** Railway, Heroku, VPS, Replit

---

## 🚀 Setup Instructions

1. Clone the repository:
```bash
git clone https://github.com/yourusername/bot-collection.git
cd bot-collection
```

2. Install dependencies:
```bash
pip install -r requirements.txt
```

3. Add `.env` file or config with tokens for:
- Discord Bot Token
- Telegram Bot Token
- MongoDB URI
- Flask Secret Key

4. Run individual bots as needed:
```bash
# For Discord Bubble Bot
python BubbleBot/main.py

# For Telegram Giveaway Bot
python GiveawayBotV2/bot.py

# For Flask Points Site
python FlaskSite/app.py
```

---

## 📁 Repository Layout

```
bot-collection/
├── Bubble Bot Discord/
├── Giveaway tele bot v1/
├── Giveaway tele bot v2/
├── Moderation bot Discord (old)/
├── requirements.txt
└── README.md
```

---

## 📫 Contact

- **Portfolio:** [bijaykoirala0.com.np](https://bijaykoirala0.com.np)
- **Telegram:** [@flamemodparadise](https://t.me/flamemodparadise)
- **Email:** bijaykoirala162@gmail.com

---

> 🧠 Built with passion, tested in real Discord and Telegram communities. Feedback and collaboration are welcome!
