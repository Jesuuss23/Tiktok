import asyncio
import requests
from TikTokLive import TikTokLiveClient
from TikTokLive.events import ConnectEvent, CommentEvent

# CONFIGURACIÓN
USERNAME = "@officialfabiojackson"
LARAVEL_URL = "https://auto.donguando.com/tiktok-api/api/live-message"
API_KEY = "tu_clave_secreta_aqui"

client = TikTokLiveClient(unique_id=USERNAME)

@client.on(ConnectEvent)
async def on_connect(event):
    print(f"✅ Conectado al LIVE de @{client.unique_id}")

@client.on(CommentEvent)
async def on_comment(event):
    print(f"💬 {event.user.nickname}: {event.comment}")
    # Enviar a Laravel
    try:
        response = requests.post(
            LARAVEL_URL,
            json={
                'live_id': client.room_id,
                'sender_id': event.user.user_id,
                'sender_username': event.user.nickname,
                'content': event.comment,
            },
            headers={
                'X-API-Key': API_KEY,
                'Content-Type': 'application/json'
            },
            timeout=5
        )
        print(f"   → Enviado a Laravel: {response.status_code}")
    except Exception as e:
        print(f"   → Error enviando: {e}")

if __name__ == '__main__':
    print(f"🔍 Conectando a {USERNAME}...")
    client.run()
