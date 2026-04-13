# Comment System Implementation

## Overview
Implemented a complete comment system with threaded replies, likes, and user authentication. Includes a new CUSTOMER role for regular users who can comment but cannot access the admin dashboard.

## Features Implemented

### 1. Database Structure

#### Comments Table
- `id` - Primary key
- `user_id` - Foreign key to users
- `anime_id` - Foreign key to animes (nullable)
- `episode_id` - Foreign key to episodes (nullable)
- `parent_id` - Foreign key to comments (nullable, for replies)
- `comment` - Text content (max 1000 characters)
- `likes_count` - Counter cache for likes
- `is_approved` - Boolean for moderation
- `created_at` / `updated_at` - Timestamps

**Indexes:**
- `anime_id + created_at` - Fast anime comment queries
- `episode_id + created_at` - Fast episode comment queries
- `parent_id` - Fast reply lookups

#### Comment Likes Table
- `id` - Primary key
- `user_id` - Foreign key to users
- `comment_id` - Foreign key to comments
- `created_at` / `updated_at` - Timestamps

**Constraints:**
- Unique constraint on `user_id + comment_id` (one like per user per comment)

### 2. Models

#### Comment Model
**Relationships:**
- `user()` - BelongsTo User
- `anime()` - BelongsTo Anime
- `episode()` - BelongsTo Episode
- `parent()` - BelongsTo Comment (parent comment)
- `replies()` - HasMany Comment (child comments)
- `likedBy()` - BelongsToMany User

**Methods:**
- `isLikedBy(User $user)` - Check if user has liked the comment

**Features:**
- Auto-loads `user` relationship with queries
- Supports nested replies (threaded comments)

### 3. API Endpoints

#### Public Endpoints (No Authentication)
```
GET /api/v1/comments/anime/{slug}
GET /api/v1/comments/episode/{animeSlug}/{episodeNumber}
```

#### Protected Endpoints (Require Authentication)
```
POST /api/v1/comments
PUT /api/v1/comments/{id}
DELETE /api/v1/comments/{id}
POST /api/v1/comments/{id}/like
```

### 4. User Roles & Permissions

#### CUSTOMER Role
**Created with:**
- Name: `CUSTOMER`
- Guard: `web`

**Permissions:**
- ✅ Can view public content
- ✅ Can post comments
- ✅ Can reply to comments
- ✅ Can like/unlike comments
- ✅ Can update own comments
- ✅ Can delete own comments
- ❌ **Cannot access Filament admin dashboard**

**Implementation:**
```php
public function canAccessPanel(\Filament\Panel $panel): bool
{
    if ($this->hasRole('CUSTOMER')) {
        return false;
    }
    return true;
}
```

### 5. Sample Data

#### Test Users (CUSTOMER Role)
| Name | Email | Password |
|------|-------|----------|
| John Doe | john@example.com | password123 |
| Jane Smith | jane@example.com | password123 |
| Mike Johnson | mike@example.com | password123 |
| Sarah Williams | sarah@example.com | password123 |
| Chris Brown | chris@example.com | password123 |

#### Seeded Comments
- 28 sample comments created
- 15 anime comments
- 10 episode comments
- 3 comment replies
- Random likes (0-50 per comment)

## API Usage Examples

### 1. Get Anime Comments
```bash
curl 'http://localhost:8000/api/v1/comments/anime/one-piece' \
  -H 'Accept: application/json'
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
        "name": "John Doe"
      },
      "comment": "This anime is amazing!",
      "likes_count": 42,
      "replies": [
        {
          "id": 2,
          "user": { "name": "Jane Smith" },
          "comment": "I agree!"
        }
      ],
      "created_at": "2026-04-12T10:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 15
  }
}
```

### 2. Post a Comment (Authenticated)
```bash
curl -X POST 'http://localhost:8000/api/v1/comments' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "anime_id": 1,
    "comment": "Great anime!"
  }'
```

### 3. Reply to a Comment (Authenticated)
```bash
curl -X POST 'http://localhost:8000/api/v1/comments' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "anime_id": 1,
    "parent_id": 5,
    "comment": "I totally agree!"
  }'
```

### 4. Like a Comment (Authenticated)
```bash
curl -X POST 'http://localhost:8000/api/v1/comments/1/like' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Accept: application/json'
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

### 5. Update a Comment (Authenticated)
```bash
curl -X PUT 'http://localhost:8000/api/v1/comments/1' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "comment": "Updated comment text"
  }'
```

### 6. Delete a Comment (Authenticated)
```bash
curl -X DELETE 'http://localhost:8000/api/v1/comments/1' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Accept: application/json'
```

## Security Features

1. **Authentication Required** - Only authenticated users can create, update, delete, or like comments
2. **Ownership Validation** - Users can only update/delete their own comments
3. **Input Validation** - Comments limited to 1000 characters
4. **SQL Injection Prevention** - Using Eloquent ORM
5. **Role-Based Access** - CUSTOMER role cannot access admin panel

## Files Created/Modified

### New Files
1. `database/migrations/2026_04_12_162150_create_comments_table.php`
2. `database/migrations/2026_04_12_162214_create_comment_likes_table.php`
3. `app/Models/Comment.php`
4. `app/Http/Controllers/Api/CommentController.php`
5. `database/seeders/CustomerUsersSeeder.php`
6. `scripts/verify_comment_system.php`

### Modified Files
1. `routes/api.php` - Added comment routes
2. `app/Models/User.php` - Added `canAccessPanel()` method
3. `API_DOCUMENTATION.md` - Added comment endpoint documentation

## Database Stats
- 5 customer users created
- 28 sample comments
- Comments distributed across first 5 anime
- Threaded replies implemented
- Like functionality tested

## Testing

Run the verification script:
```bash
php scripts/verify_comment_system.php
```

Test API endpoints:
```bash
# Get comments for an anime
php artisan serve
curl http://localhost:8000/api/v1/comments/anime/attack-on-titan-final-season
```

## Integration with Frontend

1. **Authentication**: Use Laravel Sanctum tokens
2. **Real-time updates**: Consider adding WebSocket support later
3. **Pagination**: All comment endpoints return paginated results
4. **Nested replies**: Comments include `replies` array with nested data

## Future Enhancements

- [ ] Comment moderation system
- [ ] Report spam/abuse functionality
- [ ] Emoji reactions (beyond just likes)
- [ ] Mention system (@username)
- [ ] Rich text formatting
- [ ] Real-time notifications
- [ ] Comment search
- [ ] Sort options (newest, popular, oldest)
