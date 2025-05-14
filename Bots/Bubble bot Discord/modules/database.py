import pymongo
from datetime import datetime
from pytz import utc

MONGODB_CONNECTION_STRING = "mongodb+srv://Bijay:bijay%40123@cluster0.hpl6qfx.mongodb.net/db_discord?retryWrites=true&w=majority"

def fetch_or_register_user(userid):
    try:
        client = pymongo.MongoClient(MONGODB_CONNECTION_STRING)
        db = client['db_discord']
        collection = db['tbl_discord']
        user = collection.find_one({'userid': userid})
        if user:
            print(f"User {userid} found in MongoDB.")
            return user
        new_user = {
            'userid': userid,
            'createdat': datetime.now(utc),
            'updatedat': datetime.now(utc),
            'points': 2
        }
        collection.insert_one(new_user)
        print(f"User {userid} registered in MongoDB.")
        return new_user
    except Exception as e:
        print(f'Error querying/registering user in MongoDB: {e}')
        return None
    finally:
        client.close()
        print("MongoDB connection closed.")
