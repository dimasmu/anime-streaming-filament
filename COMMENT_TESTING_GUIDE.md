## Comment System - Quick Test Guide

### Test Customer Users
All test users have the same password: `password123`

| Name | Email | Role |
|------|-------|------|
| John Doe | john@example.com | CUSTOMER |
| Jane Smith | jane@example.com | CUSTOMER |
| Mike Johnson | mike@example.com | CUSTOMER |
| Sarah Williams | sarah@example.com | CUSTOMER |
| Chris Brown | chris@example.com | CUSTOMER |

### Test API Endpoints (No Auth Required)

#### 1. Get Anime Comments
```bash
curl http://localhost:8000/api/v1/comments/anime/attack-on-titan-final-season
```

#### 2. Get Episode Comments
```bash
curl http://localhost:8000/api/v1/comments/episode/attack-on-titan-final-season/1
```

### Test With Authentication

#### 1. Login to Get Token
First, you'll need to implement login endpoint or use Laravel Sanctum:
```bash
# You'll need to create a login endpoint first
POST /api/v1/auth/login
{
  "email": "john@example.com",
  "password": "password123"
}
```

#### 2. Post a Comment
```bash
curl -X POST http://localhost:8000/api/v1/comments \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "anime_id": 1,
    "comment": "This is my first comment!"
  }'
```

#### 3. Reply to a Comment
```bash
curl -X POST http://localhost:8000/api/v1/comments \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "anime_id": 1,
    "parent_id": 1,
    "comment": "Great comment!"
  }'
```

#### 4. Like a Comment
```bash
curl -X POST http://localhost:8000/api/v1/comments/1/like \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### 5. Update Comment
```bash
curl -X PUT http://localhost:8000/api/v1/comments/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "comment": "Updated comment text"
  }'
```

#### 6. Delete Comment
```bash
curl -X DELETE http://localhost:8000/api/v1/comments/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Verify Database
```bash
php scripts/verify_comment_system.php
```

### Expected Output
```
Comment System Verification
==================================================

✅ CUSTOMER role exists
   → 5 users have CUSTOMER role

Customer Users:
--------------------------------------------------
  John Doe (john@example.com)
    → 6 comments
    → Can access panel: No
  ...

Comment Statistics:
--------------------------------------------------
  Total comments: 28
  Anime comments: 15
  Episode comments: 10
  Replies: 3
```

### Dashboard Access Test

Try logging in to Filament admin panel with a CUSTOMER user:
- URL: `http://localhost:8000/admin`
- Email: `john@example.com`
- Password: `password123`

**Expected Result:** ❌ Access Denied (CUSTOMER role cannot access dashboard)

### PowerShell Quick Tests

```powershell
# Test anime comments
$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/comments/anime/one-piece"
$response.data | Select-Object -First 3

# Test episode comments
$response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/comments/episode/one-piece/1"
$response.pagination
```
