# Tasks API Reference

Base URL: `http://fad-qualification.test/api/v1`

All requests and responses are JSON. Send `Accept: application/json` so validation and
error responses come back in the envelope described below rather than as HTML.

## Response envelope

Every endpoint returns the same two shapes, produced by the response traits in
`app/Http/Responses/Concerns`.

**Success**

```json
{
  "success": true,
  "message": "Task retrieved successfully.",
  "data": { "...": "..." }
}
```

List endpoints add `links` and `meta` from the paginator alongside `data`.

**Error**

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "title": ["A task title is required."]
  }
}
```

`errors` is present only when there is field-level detail to report — a 404 or 500 omits it.

## The task object

| Field          | Type             | Notes                                     |
| -------------- | ---------------- | ----------------------------------------- |
| `id`           | integer          | Auto-increment primary key                |
| `title`        | string           | 3–255 characters, required                |
| `description`  | string \| null   | Up to 5000 characters, optional           |
| `is_completed` | boolean          | Defaults to `false`                       |
| `created_at`   | string (ISO 8601) | Read-only                                |
| `updated_at`   | string (ISO 8601) | Read-only                                |

## Endpoints

| Method       | Path         | Purpose                | Success status |
| ------------ | ------------ | ---------------------- | -------------- |
| `GET`        | `/tasks`     | List tasks (paginated) | `200`          |
| `GET`        | `/tasks/{id}` | Fetch one task        | `200`          |
| `POST`       | `/tasks`     | Create a task          | `201`          |
| `PUT\|PATCH` | `/tasks/{id}` | Update a task         | `200`          |
| `DELETE`     | `/tasks/{id}` | Delete a task         | `200`          |

`{id}` must be numeric; anything else does not match the route and returns `404`.

---

### GET /tasks

Returns a paginated list, newest first by default.

**Query parameters** (all optional)

| Parameter        | Type    | Default      | Notes                                                        |
| ---------------- | ------- | ------------ | ------------------------------------------------------------ |
| `search`         | string  | –            | Partial match against `title` and `description`              |
| `is_completed`   | boolean | –            | Accepts `true`/`false`/`1`/`0`                               |
| `sort_by`        | string  | `created_at` | One of `id`, `title`, `is_completed`, `created_at`, `updated_at` |
| `sort_direction` | string  | `desc`       | `asc` or `desc`                                              |
| `per_page`       | integer | `15`         | Between 1 and 100                                            |

```http
GET /api/v1/tasks?search=runbook&is_completed=false&sort_by=title&sort_direction=asc&per_page=10
```

```json
{
  "success": true,
  "message": "Tasks retrieved successfully.",
  "data": [
    {
      "id": 1,
      "title": "Write the deployment runbook",
      "description": "Cover rollback steps.",
      "is_completed": false,
      "created_at": "2026-09-28T17:34:33+00:00",
      "updated_at": "2026-09-28T17:34:33+00:00"
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": null },
  "meta": { "current_page": 1, "per_page": 10, "total": 1, "last_page": 1, "from": 1, "to": 1 }
}
```

An invalid `sort_by` or `per_page` returns `422`.

---

### GET /tasks/{id}

```json
{
  "success": true,
  "message": "Task retrieved successfully.",
  "data": {
    "id": 1,
    "title": "Write the deployment runbook",
    "description": "Cover rollback steps.",
    "is_completed": false,
    "created_at": "2026-09-28T17:34:33+00:00",
    "updated_at": "2026-09-28T17:34:33+00:00"
  }
}
```

Missing task → `404`:

```json
{ "success": false, "message": "Task [424242] was not found." }
```

---

### POST /tasks

**Body**

| Field          | Rules                                  |
| -------------- | -------------------------------------- |
| `title`        | required, string, min 3, max 255       |
| `description`  | optional, nullable string, max 5000    |
| `is_completed` | optional boolean, defaults to `false`  |

```json
{
  "title": "Ship the tasks API",
  "description": "Repository, service and resource layers.",
  "is_completed": false
}
```

Responds `201` with the created task. Validation failure returns `422`:

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field must be at least 3 characters."],
    "is_completed": ["The is completed field must be true or false."]
  }
}
```

---

### PUT / PATCH /tasks/{id}

Both verbs hit the same action. The difference is only in validation:

- **`PUT`** treats `title` as required — you state the title the task should end up with.
- **`PATCH`** makes every field optional, for partial updates.

In both cases **only the fields present in the payload are written**, so omitting
`description` leaves the stored value untouched. To clear it, send it explicitly as `null`.

```json
{ "is_completed": true }
```

Responds `200` with the updated task. Missing task → `404`.

---

### DELETE /tasks/{id}

Responds `200` with a confirmation and no `data` key:

```json
{ "success": true, "message": "Task deleted successfully." }
```

Missing task → `404`.

## Status codes

| Code  | When                                                              |
| ----- | ----------------------------------------------------------------- |
| `200` | Successful read, update or delete                                  |
| `201` | Task created                                                      |
| `404` | Task does not exist, or the endpoint/route does not match          |
| `405` | Endpoint exists but not for that HTTP verb                         |
| `422` | Request body or query string failed validation                      |
| `500` | Unexpected server error (details hidden unless `APP_DEBUG=true`)   |
