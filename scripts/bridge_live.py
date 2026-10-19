#!/usr/bin/env python3
import sys
import json
import asyncio
import argparse
import requests
from TikTokLive import TikTokLiveClient
from TikTokLive.events import ConnectEvent, CommentEvent

# Parsear argumentos
parser = argparse.ArgumentParser(description='Puente TikTok LIVE -> Laravel')
parser.add_argument('--username', required=True, help='@username del LIVE')
parser.add_argument('--url', required=True, help='URL del endpoint Laravel')
parser.add_argument('--api-key', required=True, help='API Key para autenticar')
args = parser.parse_args()

USERNAME = args.username.replace('@', '')
LARAVEL_URL = args.url
API_KEY = args.api_key

client = TikTokLiveClient(unique_id=f"@{USERNAME}")

@client.on(ConnectEvent)
async def on_connect(event):
    print(f"✅ Conectado al LIVE de @{USERNAME} (Room ID: {client.room_id})")

@client.on(CommentEvent)
async def on_comment(event):
    nickname = event.user.nickname
    comment = event.comment
    user_id = event.user.unique_id
    
    print(f"💬 {nickname}: {comment}")
    
    # Enviar a Laravel
    try:
        response = requests.post(
            LARAVEL_URL,
            json={
                'live_id': str(client.room_id),
                'sender_id': str(user_id),
                'sender_username': nickname,
                'content': comment,
            },
            headers={
                'X-API-Key': API_KEY,
                'Content-Type': 'application/json'
            },
            timeout=5
        )
        
        if response.status_code == 200:
            result = response.json()
            status = result.get('result', {}).get('action', '?')
            print(f"   → [{status}] Enviado a Laravel")
        else:
            print(f"   → Error HTTP {response.status_code}")
            
    except requests.exceptions.Timeout:
        print(f"   → Timeout enviando a Laravel")
    except Exception as e:
        print(f"   → Error: {e}")

if __name__ == '__main__':
    print(f"🔍 Conectando a @{USERNAME}...")
    print(f"📡 Endpoint: {LARAVEL_URL}")
    client.run()
