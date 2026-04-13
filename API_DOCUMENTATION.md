# Streaming Website API Documentation

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
Currently, all endpoints are public and do not require authentication.

## Response Format
All responses follow this format:

### Success Response
```json
{
    "success": true,
    "data": { ... }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message"
}
```

## Endpoints

### Anime Endpoints

#### Get All Animes
```
GET /api/v1/animes
```

Query Parameters:
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 12)
- `genre` - Filter by genre slug
- `studio` - Filter by studio slug
- `category` - Filter by category slug
- `type` - Filter by type (TV, Movie, OVA, ONA, Special)
- `status` - Filter by status (ongoing, completed)
- `year` - Filter by release year
- `search` - Search in title and description
- `sort_by` - Sort field (created_at, rating, title, release_date)
- `sort_order` - Sort order (asc, desc)

Example:
```
GET /api/v1/animes?genre=action&type=TV&sort_by=rating&sort_order=desc
```

#### Get Featured Animes
```
GET /api/v1/animes/featured
```
Returns top 10 featured animes sorted by rating.

#### Get Latest Animes
```
GET /api/v1/animes/latest
```
Returns latest 12 animes.

#### Get Trending Animes
```
GET /api/v1/animes/trending
```
Returns top 10 trending animes.

#### Get Top 10 Anime - Today
```
GET /api/v1/animes/top/today
```
Returns top 10 anime with most views in the last 24 hours.

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
      "genres": [...],
      "studio": {...}
    }
  ]
}
```

#### Get Top 10 Anime - Week
```
GET /api/v1/animes/top/week
```
Returns top 10 anime with most views in the last 7 days.

#### Get Top 10 Anime - Month
```
GET /api/v1/animes/top/month
```
Returns top 10 anime with most views in the last 30 days.

#### Get Anime by Slug
```
GET /api/v1/animes/{slug}
```
Returns detailed anime information including episodes.

Example:
```
GET /api/v1/animes/one-piece
```

#### Get Anime Episodes
```
GET /api/v1/animes/{slug}/episodes
```
Returns all episodes for an anime.

#### Get Episodes with Anime Details by Slug
```
GET /api/v1/animes/episode/{slug}
```
Returns comprehensive anime details along with simplified episode list and navigation URLs.

**Response:**
```json
{
    "success": true,
    "data": {
        "anime": {
            "id": 1,
            "title": "Anime Title",
            "slug": "anime-slug",
            "description": "...",
            "synopsis": "...",
            "poster_image_url": "...",
            "cover_image_url": "...",
            "trailer_url": "...",
            "type": "TV",
            "status": "ongoing",
            "episodes_count": 12,
            "duration": "24 min",
            "release_date": "2026-01-01",
            "rating": 8.5,
            "views": 10000,
            "genres": [...],
            "studio": {...},
            "categories": [...]
        },
        "episodes": [
            {
                "id": 13162,
                "episode_number": 1,
                "next_episode_url": "http://localhost:8000/api/v1/watch/anime-slug/2"
            },
            {
                "id": 13163,
                "episode_number": 2,
                "previous_episode_url": "http://localhost:8000/api/v1/watch/anime-slug/1",
                "next_episode_url": "http://localhost:8000/api/v1/watch/anime-slug/3"
            },
            {
                "id": 13173,
                "episode_number": 12,
                "previous_episode_url": "http://localhost:8000/api/v1/watch/anime-slug/11"
            }
        ],
        "total_episodes": 12
    }
}
```

**Notes:**
- Episodes include only `id`, `episode_number`, and navigation URLs
- First episode has only `next_episode_url`
- Last episode has only `previous_episode_url`
- Middle episodes have both `previous_episode_url` and `next_episode_url`
- Navigation URLs point to the `/api/v1/watch/{slug}/{episodeNumber}` endpoint

Example:
```
GET /api/v1/animes/episode/b-daman-crossfire-6bf9ee
```

### Episode Endpoints

#### Get Latest Episodes
```
GET /api/v1/episodes/latest
```
Returns latest 20 episodes.

#### Get Episode by ID
```
GET /api/v1/episodes/{id}
```
Returns detailed episode information.

#### Increment Episode Views
```
POST /api/v1/episodes/{id}/view
```
Increments the view count for an episode.

#### Toggle Episode Like
```
POST /api/v1/episodes/{id}/like
```

Body:
```json
{
    "action": "increment" // or "decrement"
}
```

#### Watch Episode
```
GET /api/v1/watch/{animeSlug}/{episodeNumber}
```
Returns episode data for the video player. Automatically increments views.

Example:
```
GET /api/v1/watch/one-piece/1
```

### Comment Endpoints

#### Get Anime Comments
```
GET /api/v1/comments/get/anime/{slug}
```
Returns paginated comments for an anime.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "anime_id": 1,
      "episode_id": null,
      "parent_id": null,
      "comment": "This anime is absolutely amazing!",
      "likes_count": 42,
      "is_approved": true,
      "created_at": "2026-04-12T10:30:00.000000Z",
      "user": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "replies": [
        {
          "id": 2,
          "parent_id": 1,
          "comment": "I totally agree with you!",
          "user": { "name": "Jane Smith" }
        }
      ]
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
Returns paginated comments for a specific episode.

**Example:**
```
GET /api/v1/comments/get/episode/one-piece/1
```

#### Save Comment (Auth Required)
```
POST /api/v1/comments/save
Authorization: Bearer {token}
```

**Body:**
```json
{
  "anime_id": 1,           // Optional: for anime comments
  "episode_id": 10,        // Optional: for episode comments
  "parent_id": 5,          // Optional: for replies
  "comment": "Great episode!"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Comment posted successfully",
  "data": {
    "id": 123,
    "comment": "Great episode!",
    "user": { "name": "John Doe" }
  }
}
```

#### Update Comment (Auth Required)
```
PUT /api/v1/comments/save/{id}
Authorization: Bearer {token}
```

**Body:**
```json
{
  "comment": "Updated comment text"
}
```

**Note:** Users can only update their own comments.

#### Delete Comment (Auth Required)
```
DELETE /api/v1/comments/save/{id}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Comment deleted successfully"
}
```

**Note:** Users can only delete their own comments. Deleting a comment also deletes all replies.

#### Like/Unlike Comment (Auth Required)
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

**Note:** Toggles like status. If already liked, it unlikes.

### Genre Endpoints

#### Get All Genres
```
GET /api/v1/genres
```
Returns all genres with anime counts.

#### Get Genre by Slug
```
GET /api/v1/genres/{slug}
```
Returns genre details.

#### Get Animes by Genre
```
GET /api/v1/genres/{slug}/animes
```

Query Parameters:
- `page` - Page number
- `per_page` - Items per page (default: 12)
- `type` - Filter by type
- `status` - Filter by status
- `sort_by` - Sort field
- `sort_order` - Sort order

### Studio Endpoints

#### Get All Studios
```
GET /api/v1/studios
```
Returns all active studios with anime counts.

#### Get Studio by Slug
```
GET /api/v1/studios/{slug}
```
Returns studio details.

#### Get Animes by Studio
```
GET /api/v1/studios/{slug}/animes
```

Query Parameters: Same as genre animes endpoint.

### Category Endpoints

#### Get All Categories
```
GET /api/v1/categories
```
Returns all categories with anime counts.

#### Get Category by Slug
```
GET /api/v1/categories/{slug}
```
Returns category details.

#### Get Animes by Category
```
GET /api/v1/categories/{slug}/animes
```

Query Parameters: Same as genre animes endpoint.

### Search Endpoint

#### Search Animes
```
GET /api/v1/search?q={search_term}
```
Searches in title, description, and synopsis.

Example:
```
GET /api/v1/search?q=one piece
```

## Data Models

### Anime Model
```json
{
    "id": 1,
    "title": "Anime Title",
    "slug": "anime-title",
    "description": "Description",
    "synopsis": "Synopsis",
    "poster_image": "path/to/poster.jpg",
    "poster_image_url": "http://localhost:8000/storage/path/to/poster.jpg",
    "cover_image": "path/to/cover.jpg",
    "cover_image_url": "http://localhost:8000/storage/path/to/cover.jpg",
    "trailer_url": "https://youtube.com/watch?v=xxx",
    "status": "ongoing",
    "type": "TV",
    "episodes_count": 12,
    "actual_episodes_count": 10,
    "is_complete": false,
    "duration": "24 min",
    "release_date": "2024-01-01",
    "rating": "8.5",
    "views": 1000,
    "is_featured": true,
    "is_published": true,
    "studio": {
        "id": 1,
        "name": "Studio Name",
        "slug": "studio-name"
    },
    "genres": [
        {
            "id": 1,
            "name": "Action",
            "slug": "action"
        }
    ],
    "categories": [
        {
            "id": 1,
            "name": "Shounen",
            "slug": "shounen"
        }
    ],
    "episodes": [...]
}
```

### Episode Model
```json
{
    "id": 1,
    "anime_id": 1,
    "title": "Episode Title",
    "episode_number": 1,
    "description": "Episode description",
    "thumbnail": "path/to/thumbnail.jpg",
    "video_url": "https://youtube.com/watch?v=xxx",
    "duration": "24:00",
    "air_date": "2024-01-01",
    "is_published": true,
    "likes": 100,
    "views": 1000,
    "anime": {...}
}
```

## Testing the API

You can test the API using:
- Browser (for GET requests)
- Postman
- cURL
- Or your React frontend

Example cURL command:
```bash
curl http://localhost:8000/api/v1/animes
```

## CORS Configuration

To allow your React frontend to access the API, make sure CORS is configured in:
`config/cors.php` or `bootstrap/app.php`

## Next Steps

1. Update your React streaming website to fetch data from these API endpoints
2. Replace the static `animeData.ts` with API calls
3. Implement loading states and error handling
4. Add authentication if needed for user-specific features (watch history, favorites, etc.)
