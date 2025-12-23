import requests
import os
import random
from time import sleep

# Configuration
OUTPUT_DIR = "avatars_mixed"
NUM_REAL = 50       # realistic faces
NUM_ANIME = 50      # anime avatars
NUM_PICSUM = 50     # landscapes/abstract

# Make folders
os.makedirs(OUTPUT_DIR + "/real_faces", exist_ok=True)
os.makedirs(OUTPUT_DIR + "/anime", exist_ok=True)
os.makedirs(OUTPUT_DIR + "/picsum", exist_ok=True)

# --- 1️⃣ Realistic human faces ---
print("Downloading realistic faces...")
for i in range(1, NUM_REAL + 1):
    try:
        r = requests.get("https://thispersondoesnotexist.com/", timeout=10)
        r.raise_for_status()
        with open(f"{OUTPUT_DIR}/real_faces/face_{i}.jpg", "wb") as f:
            f.write(r.content)
        print(f"Saved face_{i}.jpg")
        sleep(0.5)  # polite to the server
    except Exception as e:
        print(f"Failed face_{i}: {e}")

# --- 2️⃣ Anime avatars ---
print("\nDownloading anime avatars...")
# Using Waifu API - rotating seed from 1-10 for variety
for i in range(1, NUM_ANIME + 1):
    try:
        seed = random.randint(1, 10)
        r = requests.get(f"https://www.thiswaifudoesnotexist.net/example-{seed}.jpg", timeout=10)
        r.raise_for_status()
        with open(f"{OUTPUT_DIR}/anime/waifu_{i}.jpg", "wb") as f:
            f.write(r.content)
        print(f"Saved waifu_{i}.jpg")
        sleep(0.5)
    except Exception as e:
        print(f"Failed waifu_{i}: {e}")

# --- 3️⃣ Landscapes / abstract (Picsum) ---
print("\nDownloading Picsum images...")
for i in range(1, NUM_PICSUM + 1):
    try:
        r = requests.get("https://picsum.photos/200/200", timeout=10)
        r.raise_for_status()
        with open(f"{OUTPUT_DIR}/picsum/pic_{i}.jpg", "wb") as f:
            f.write(r.content)
        print(f"Saved pic_{i}.jpg")
        sleep(0.2)
    except Exception as e:
        print(f"Failed pic_{i}: {e}")

print("\nAll downloads complete!")
