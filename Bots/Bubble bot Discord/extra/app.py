from datetime import datetime, timedelta, timezone
from flask import Flask, jsonify, render_template, request, flash
from flask_cors import CORS
from pymongo import MongoClient
import pymongo

app = Flask(__name__)
CORS(app, resources={r"/submit": {"origins": "https://fmp-discord.github.io"}})
app.secret_key = 'your_secret_key_here'  # Replace with a secure secret key

# MongoDB connection URI for MongoDB Atlas cluster
uri = "mongodb+srv://Bijay:bijay%40123@cluster0.hpl6qfx.mongodb.net/db_discord?retryWrites=true&w=majority"

# Create a new client and connect to the server
client = MongoClient(uri)

# Database and collection names
db = client.db_discord
users_collection = db.tbl_discord

# Ensure the collection exists, create it if it doesn't
if "tbl_discord" not in db.list_collection_names():
    users_collection = db.create_collection("tbl_discord")
else:
    users_collection = db.tbl_discord

# Define UTC timezone
utc = timezone.utc

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/submit', methods=['POST'])
def submit():
    data = request.json  # Assuming the request contains JSON data

    try:
        discord_id = int(data['discord_id'])  # Convert to integer
    except ValueError:
        return jsonify({"message": "Invalid Discord UserID. Please enter a valid number."}), 400

    current_time = datetime.now(timezone.utc)  # Ensure current_time is UTC-aware

    try:
        # Check if the user exists in MongoDB
        user = users_collection.find_one({"userid": discord_id})

        if user:
            # User exists, check updatedat (last submit time) and points
            updated_at = user.get('updatedat', None)
            user_points = user.get('points', 0)

            if updated_at:
                # Ensure updated_at is datetime object with timezone
                if isinstance(updated_at, datetime) and not updated_at.tzinfo:
                    updated_at = updated_at.replace(tzinfo=utc)

                # Check if the user is in cooldown period (1 hour)
                if (current_time - updated_at) < timedelta(hours=1):
                    cooldown_time_minutes = int((updated_at + timedelta(hours=1) - current_time).total_seconds() / 60)
                    return jsonify({
                        "message": f"You are in cooldown for {cooldown_time_minutes} minutes.",
                        "total_points": user_points
                    }), 429  # HTTP 429 Too Many Requests

            # Update user document with new points and updatedat
            new_points = user_points + 2
            users_collection.update_one({"userid": discord_id}, {
                "$set": {
                    "updatedat": current_time,
                    "points": new_points
                }
            })
        else:
            # User doesn't exist, register new user
            new_user = {
                "userid": discord_id,
                "createdat": current_time,
                "updatedat": current_time,
                "points": 2
            }
            users_collection.insert_one(new_user)

    except Exception as e:
        return jsonify({"message": f"Error processing submission: {e}"}), 500  # HTTP 500 Internal Server Error

    return jsonify({
        "message": "Thanks! You got 2 points.",
        "total_points": new_points if 'new_points' in locals() else 2  # Default to 2 if new_points not set
    })

if __name__ == '__main__':
    app.run(debug=True)
