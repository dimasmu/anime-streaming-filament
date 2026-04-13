# MAL Columns Removal Summary

## Changes Made

### Database Migration
Created migration: `2026_04_12_100000_remove_mal_columns_from_animes.php`

**Removed Columns:**
- `mal_id` - MyAnimeList ID (with unique index)
- `mal_url` - MyAnimeList URL
- `mal_season` - Broadcast season (winter/spring/summer/fall)
- `mal_year` - Broadcast year
- `mal_episodes_count` - Episode count from MAL
- `mal_last_synced_at` - Last sync timestamp
- `mal_franchise_root_id` - Franchise root anime ID (with composite index)
- `mal_franchise_last_synced_at` - Franchise sync timestamp

**Renamed Columns:**
- `mal_season_number` → `season_number` (e.g., Season 1, Season 2, Season 3)
- `mal_seasons_total` → `seasons_total` (e.g., 3 of 6 seasons)

### Model Updates
Updated `app/Models/Anime.php`:
- Removed all `mal_*` fields from `$fillable` array
- Added `season_number` and `seasons_total` to `$fillable`
- Removed `mal_last_synced_at` and `mal_franchise_last_synced_at` from `$casts`

### Controller Updates
Updated `app/Http/Controllers/Api/AnimeController.php`:
- Added `season_number` and `seasons_total` to API response in `getEpisodesBySlug()` method

### Seeder Updates
Updated seeders to remove MAL field assignments:
- `database/seeders/CuratedOfflineAnimeSeeder.php`
- `database/seeders/BulkOfflineAnimeSeeder.php`

Both seeders now use only `season_number` and `seasons_total` (set to null by default).

## Verification Results

### Database Schema ✅
- All MAL columns removed successfully
- New `season_number` and `seasons_total` columns exist
- No columns starting with `mal_` remain

### Data Preservation ✅
- Existing season data preserved (e.g., Attack on Titan: Season 5 of 6)
- All anime records intact

### API Response ✅
Example response now includes:
```json
{
  "id": 1,
  "title": "Attack on Titan: The Final Season",
  "slug": "attack-on-titan-final-season",
  "season_number": 5,
  "seasons_total": 6,
  "status": "completed",
  "rating": 9.1,
  "episodes_count": 12
}
```

## Impact
- Database simplified by removing 8 unused columns
- Cleaner data model without external API dependencies
- Season tracking still available via `season_number` and `seasons_total`
- No API breaking changes (MAL fields were not exposed in public API)

## Rollback
If needed, run: `php artisan migrate:rollback --step=1`

This will restore all MAL columns and revert the season column renames.
