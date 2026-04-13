# New API Endpoint: Get Episodes with Anime Details

## Endpoint
```
GET /api/v1/animes/episode/{slug}
```

## Description
Returns comprehensive anime details along with all published episodes including video URLs.

## Example Usage

### cURL
```bash
curl 'http://localhost:8000/api/v1/animes/episode/b-daman-crossfire-6bf9ee' \
  -H 'Accept: application/json'
```

### JavaScript/Fetch
```javascript
fetch('http://localhost:8000/api/v1/animes/episode/one-piece', {
  headers: {
    'Accept': 'application/json'
  }
})
  .then(response => response.json())
  .then(data => {
    console.log('Anime:', data.data.anime.title);
    console.log('Episodes:', data.data.total_episodes);
    console.log('Episode list:', data.data.episodes);
  });
```

### PowerShell
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/v1/animes/episode/one-piece" -Headers @{"Accept"="application/json"}
```

## Response Structure
```json
{
  "success": true,
  "data": {
    "anime": {
      "id": 1071,
      "title": "B-Daman CrossFire",
      "slug": "b-daman-crossfire-6bf9ee",
      "description": null,
      "synopsis": null,
      "poster_image": null,
      "poster_image_url": null,
      "cover_image": null,
      "cover_image_url": null,
      "trailer_url": null,
      "type": "tv",
      "status": "upcoming",
      "episodes_count": 12,
      "duration": null,
      "release_date": null,
      "rating": null,
      "views": 77323,
      "source": null,
      "is_featured": false,
      "genres": [],
      "studio": null,
      "categories": []
    },
    "episodes": [
      {
        "id": 13162,
        "episode_number": 1,
        "title": "Episode 1",
        "description": null,
        "thumbnail": null,
        "video_url": null,
        "duration": null,
        "air_date": null,
        "views": 1,
        "likes": 0,
        "created_at": "2026-04-12T10:25:48.000000Z",
        "updated_at": "2026-04-12T12:24:31.000000Z"
      },
      // ... more episodes
    ],
    "total_episodes": 12
  }
}
```

## Benefits
- Single API call to get both anime details and all episodes
- Includes video URLs for direct playback
- Returns only published episodes
- Includes episode metadata (views, likes, duration, etc.)
- Comprehensive anime information with related data (genres, studio, categories)

## Use Cases
1. Video player initialization - get all episodes for a series
2. Episode list display - show all available episodes
3. Binge-watch features - access sequential episode data
4. Mobile apps - reduce API calls by getting all data at once
