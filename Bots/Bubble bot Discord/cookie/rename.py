import os
import logging

# Set up logging
logging.basicConfig(filename='rename_log.txt', level=logging.INFO, 
                    format='%(asctime)s - %(levelname)s - %(message)s')

# Define the directory
directory = r"D:\discord\cookie\netflix"

try:
    # Change to the directory
    os.chdir(directory)
except FileNotFoundError as e:
    logging.error(f"Directory not found: {directory}")
    print(f"Error: Directory '{directory}' not found.")
    exit(1)

# Get all files in the directory
files = [f for f in os.listdir(directory) if os.path.isfile(os.path.join(directory, f))]

# Initialize the counter
counter = 1

# Loop through files and rename them
for file_name in files:
    try:
        # Construct the new file name
        new_name = f"fmpnetflixcookie{counter}.txt"
        
        # Check if the new file name already exists to avoid overwriting
        if os.path.exists(new_name):
            logging.warning(f"File '{new_name}' already exists. Skipping.")
            print(f"Warning: File '{new_name}' already exists. Skipping.")
            counter += 1
            continue
        
        # Rename the file
        os.rename(file_name, new_name)
        logging.info(f"Renamed '{file_name}' to '{new_name}'")
        print(f"Renamed '{file_name}' to '{new_name}'")
        counter += 1
    except Exception as e:
        logging.error(f"Failed to rename '{file_name}': {e}")
        print(f"Error: Failed to rename '{file_name}': {e}")

print("Renaming complete.")
