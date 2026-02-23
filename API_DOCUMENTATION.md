# API Documentation - Comment & Bookmark Endpoints

## Base URL
```
http://your-domain.com/api
```

## Authentication

All protected endpoints require a Bearer token in the Authorization header:

```http
Authorization: Bearer {your_token}
```

### Login Endpoint
```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

Response:
```json
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com"
  }
}
```

---

## Comments API

### Get Comments (Public)
```http
GET /api/comments?anime_id={id}&episode_id={id}&without_spoilers={0|1}
```

**Query Parameters:**
- `anime_id` (optional): Filter by anime ID
- `episode_id` (optional): Filter by episode ID
- `without_spoilers` (optional): Set to 1 to exclude spoiler comments

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "content": "Great episode!",
        "is_spoiler": false,
        "created_at": "2026-02-22T13:45:00.000000Z",
        "user": {
          "id": 1,
          "name": "John Doe"
        },
        "anime": {
          "id": 1,
          "title": "Naruto"
        },
        "episode": {
          "id": 1,
          "title": "Episode 1"
        }
      }
    ],
    "per_page": 20,
    "total": 100
  }
}
```

### Get Single Comment (Public)
```http
GET /api/comments/{id}
```

### Create Comment (Protected)
```http
POST /api/comments
Authorization: Bearer {token}
Content-Type: application/json

{
  "anime_id": 1,
  "episode_id": 1,
  "content": "This episode was amazing!",
  "is_spoiler": false
}
```

**Required Fields:**
- Either `anime_id` OR `episode_id` must be provided
- `content`: Max 5000 characters
- `is_spoiler`: Optional, defaults to false

**Response:**
```json
{
  "success": true,
  "message": "Comment created successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "anime_id": 1,
    "episode_id": 1,
    "content": "This episode was amazing!",
    "is_spoiler": false,
    "is_visible": true,
    "created_at": "2026-02-22T13:45:00.000000Z"
  }
}
```

### Update Comment (Protected)
```http
PUT /api/comments/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "content": "Updated comment content",
  "is_spoiler": true
}
```

**Note:** Users can only update their own comments.

### Delete Comment (Protected)
```http
DELETE /api/comments/{id}
Authorization: Bearer {token}
```

**Note:** Users can only delete their own comments.

### Get My Comments (Protected)
```http
GET /api/comments/my-comments
Authorization: Bearer {token}
```

Returns all comments created by the authenticated user.

---

## Bookmarks API

All bookmark endpoints require authentication.

### Get My Bookmarks
```http
GET /api/bookmarks?anime_id={id}&episode_id={id}
Authorization: Bearer {token}
```

**Query Parameters:**
- `anime_id` (optional): Filter by anime ID
- `episode_id` (optional): Filter by episode ID

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "anime_id": 1,
        "episode_id": 1,
        "notes": "Need to rewatch this part",
        "timestamp": 1234,
        "created_at": "2026-02-22T13:45:00.000000Z",
        "anime": {
          "id": 1,
          "title": "Naruto",
          "poster_image": "path/to/image.jpg"
        },
        "episode": {
          "id": 1,
          "title": "Episode 1",
          "episode_number": 1
        }
      }
    ]
  }
}
```

### Create/Update Bookmark
```http
POST /api/bookmarks
Authorization: Bearer {token}
Content-Type: application/json

{
  "anime_id": 1,
  "episode_id": 1,
  "notes": "Great fight scene!",
  "timestamp": 1234
}
```

**Required Fields:**
- Either `anime_id` OR `episode_id` must be provided
- `notes`: Optional, max 1000 characters
- `timestamp`: Optional, position in seconds

**Note:** If a bookmark already exists for the user+anime/episode combination, it will be updated instead of creating a new one.

### Check if Bookmarked
```http
GET /api/bookmarks/check?anime_id={id}&episode_id={id}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "is_bookmarked": true,
    "bookmark": {
      "id": 1,
      "notes": "My notes",
      "timestamp": 1234
    }
  }
}
```

### Get Single Bookmark
```http
GET /api/bookmarks/{id}
Authorization: Bearer {token}
```

### Update Bookmark
```http
PUT /api/bookmarks/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "notes": "Updated notes",
  "timestamp": 2345
}
```

### Delete Bookmark
```http
DELETE /api/bookmarks/{id}
Authorization: Bearer {token}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "You do not have permission to perform this action."
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "content": [
      "The content field is required."
    ]
  }
}
```

---

## Rate Limiting

API requests are rate-limited to prevent abuse:
- 100 requests per 15 minutes per IP address
- 1000 requests per hour per authenticated user

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1645567200
```

---

## Permission Requirements

### VIEWER Role (Default User)
- ✅ View comments (public)
- ✅ Create comments
- ✅ Update own comments
- ✅ Delete own comments
- ✅ Create bookmarks
- ✅ Update own bookmarks
- ✅ Delete own bookmarks
- ✅ View own bookmarks

### MODERATOR Role
- All VIEWER permissions
- ✅ Edit/delete any comment
- ✅ View all user bookmarks

### ADMIN Role
- All permissions
