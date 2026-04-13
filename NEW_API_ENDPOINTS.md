# New API Endpoints Summary

## TOP 10 Anime Endpoints

### Daily Top 10
```
GET /api/v1/animes/top/today
```
Returns top 10 anime with most views in the last 24 hours.

**Example:**
```bash
curl http://localhost:8000/api/v1/animes/top/today
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "title": "One Piece",
      "slug": "one-piece",
      "views": 315748,
      "rating": 8.7,
      "type": "tv",
      "status": "completed",
      "episodes_count": 100,
      "genres": [...],
      "studio": {...}
    }
  ]
}
```

### Weekly Top 10
```
GET /api/v1/animes/top/week
```
Returns top 10 anime with most views in the last 7 days.

### Monthly Top 10
```
GET /api/v1/animes/top/month
```
Returns top 10 anime with most views in the last 30 days.

---

## Comment Endpoints

### Get Comments (Public)

#### Get Anime Comments
```
GET /api/v1/comments/get/anime/{slug}
```

**Example:**
```bash
curl http://localhost:8000/api/v1/comments/get/anime/one-piece
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "comment": "This anime is amazing!",
      "likes_count": 42,
      "created_at": "2026-04-12T10:30:00Z",
      "replies": [...]
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 45
  }
}
```

#### Get Episode Comments
```
GET /api/v1/comments/get/episode/{animeSlug}/{episodeNumber}
```

**Example:**
```bash
curl http://localhost:8000/api/v1/comments/get/episode/one-piece/1
```

### Save Comments (Auth Required)

#### Post New Comment
```
POST /api/v1/comments/save
Authorization: Bearer {token}
```

**Body:**
```json
{
  "anime_id": 1,
  "comment": "Great anime!"
}
```

#### Post Reply
```
POST /api/v1/comments/save
Authorization: Bearer {token}
```

**Body:**
```json
{
  "anime_id": 1,
  "parent_id": 5,
  "comment": "I agree!"
}
```

#### Update Comment
```
PUT /api/v1/comments/save/{id}
Authorization: Bearer {token}
```

**Body:**
```json
{
  "comment": "Updated text"
}
```

#### Delete Comment
```
DELETE /api/v1/comments/save/{id}
Authorization: Bearer {token}
```

#### Like/Unlike Comment
```
POST /api/v1/comments/save/{id}/like
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Comment liked",
  "data": {
    "liked": true,
    "likes_count": 43
  }
}
```

---

## Testing

### PowerShell Tests

```powershell
# Test TOP Today
$today = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/animes/top/today"
$today.data | Select-Object -First 5 | Format-Table title, views, rating

# Test TOP Week
$week = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/animes/top/week"
$week.data | Select-Object -First 5 | Format-Table title, views, rating

# Test TOP Month
$month = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/animes/top/month"
$month.data | Select-Object -First 5 | Format-Table title, views, rating

# Test Get Anime Comments
$comments = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/comments/get/anime/one-piece"
$comments.data | Select-Object -First 3
$comments.pagination

# Test Get Episode Comments
$episodeComments = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/comments/get/episode/one-piece/1"
$episodeComments.data | Select-Object -First 3
```

### cURL Tests

```bash
# Get TOP Today
curl http://localhost:8000/api/v1/animes/top/today | jq '.data[:5]'

# Get TOP Week
curl http://localhost:8000/api/v1/animes/top/week | jq '.data[:5]'

# Get TOP Month
curl http://localhost:8000/api/v1/animes/top/month | jq '.data[:5]'

# Get Anime Comments
curl http://localhost:8000/api/v1/comments/get/anime/one-piece | jq

# Get Episode Comments
curl http://localhost:8000/api/v1/comments/get/episode/one-piece/1 | jq

# Save Comment (with auth)
curl -X POST http://localhost:8000/api/v1/comments/save \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"anime_id": 1, "comment": "Great anime!"}'

# Like Comment (with auth)
curl -X POST http://localhost:8000/api/v1/comments/save/1/like \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Route Summary

### TOP 10 Routes
- `GET /api/v1/animes/top/today` - Top 10 today
- `GET /api/v1/animes/top/week` - Top 10 this week
- `GET /api/v1/animes/top/month` - Top 10 this month

### Comment Routes (GET)
- `GET /api/v1/comments/get/anime/{slug}` - Get anime comments
- `GET /api/v1/comments/get/episode/{animeSlug}/{episodeNumber}` - Get episode comments

### Comment Routes (SAVE - Auth Required)
- `POST /api/v1/comments/save` - Create comment
- `PUT /api/v1/comments/save/{id}` - Update comment
- `DELETE /api/v1/comments/save/{id}` - Delete comment
- `POST /api/v1/comments/save/{id}/like` - Like/unlike comment
