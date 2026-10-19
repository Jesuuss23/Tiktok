import sys, json, threading, time
from TikTokLive import TikTokLiveClient
from TikTokLive.events import ConnectEvent

USERNAME = sys.argv[2].replace('@', '') if len(sys.argv) > 2 else sys.argv[1].replace('@', '')

connected = False
room_id = None

client = TikTokLiveClient(unique_id=f"@{USERNAME}")

@client.on(ConnectEvent)
async def on_connect(event):
    global connected, room_id
    connected = True
    room_id = str(client.room_id)

def run_client():
    try:
        client.run()
    except:
        pass

# Ejecutar en un hilo con timeout
thread = threading.Thread(target=run_client, daemon=True)
thread.start()

# Esperar máximo 8 segundos
time.sleep(8)

if connected:
    print(json.dumps({"live": True, "username": USERNAME, "room_id": room_id}))
else:
    print(json.dumps({"live": False}))

# Forzar salida
sys.exit(0)
EOF