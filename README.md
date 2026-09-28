# Tasks API

A REST API for a simple task management system, built on Laravel 13.

Full endpoint reference: [`docs/API.md`](docs/API.md)
Postman collection: [`docs/Tasks-API.postman_collection.json`](docs/Tasks-API.postman_collection.json)

## Endpoints

| Method       | Path                 | Description            |
| ------------ | -------------------- | ---------------------- |
| `GET`        | `/api/v1/tasks`      | List tasks (paginated) |
| `GET`        | `/api/v1/tasks/{id}` | Show one task          |
| `POST`       | `/api/v1/tasks`      | Create a task          |
| `PUT\|PATCH` | `/api/v1/tasks/{id}` | Update a task          |
| `DELETE`     | `/api/v1/tasks/{id}` | Delete a task          |

A task is `id`, `title`, `description`, `is_completed`, plus `created_at` / `updated_at`.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # SQLite is the default connection
php artisan migrate --seed       # --seed is optional, it adds 17 sample tasks
php artisan serve
```

Run the suite with `php artisan test` and the formatter with `vendor/bin/pint`.

## Architecture

The request flows in one direction, and each layer has a single job:

```
Route → FormRequest → Controller → Service → Repository → Eloquent
                           ↓                     ↑
                    JsonResource          RepositoryInterface
                           ↓
                  Response traits → JSON envelope
```

| Layer                | Location                             | Responsibility                                                    |
| -------------------- | ------------------------------------ | ----------------------------------------------------------------- |
| Form requests        | `app/Http/Requests/Api/V1`           | Validate and normalise input, then hand back a DTO                |
| Controller           | `app/Http/Controllers/Api/V1`        | Translate HTTP to a service call — one expression per action       |
| Service              | `app/Services`                       | Business rules for the task use cases                             |
| Repository           | `app/Repositories`                   | All database access, behind an interface                          |
| DTOs                 | `app/DataTransferObjects`            | Immutable payload and filter objects passed between layers        |
| Resource             | `app/Http/Resources`                 | The public JSON shape of a task                                   |
| Response traits      | `app/Http/Responses/Concerns`        | The single success/error envelope                                 |
| Exceptions           | `app/Exceptions`                     | Domain failures mapped to HTTP status codes                       |

### Patterns used and why

- **Repository pattern** — `TaskRepositoryInterface` is bound to `TaskRepository` in
  `RepositoryServiceProvider`. `TaskService` depends on the interface, so the storage
  engine can be swapped or faked without touching business logic. Shared CRUD lives in
  an abstract `BaseRepository` so future repositories inherit it.
- **Service layer** — keeps controllers free of business rules and makes the use cases
  reusable from a command, queue job or another controller.
- **DTOs** — `TaskData` carries a write payload and remembers *which* attributes the
  client actually sent. That is what lets `PATCH` update one field without blanking the
  others, while still allowing `description` to be cleared with an explicit `null`.
  `TaskFilters` is a query object describing how a listing should be filtered and sorted.
- **Traits for responses** — `SendsSuccessResponses` and `SendsErrorResponses` each own
  one half of the API contract; `InteractsWithApiResponses` composes both for consumers
  that need everything. Every response in the app goes through them, so the envelope
  cannot drift between endpoints.
- **Centralised exception handling** — `ApiExceptionRenderer` is registered in
  `bootstrap/app.php` and converts validation failures, missing records, bad methods and
  unexpected errors into the same error envelope. Controllers contain no `try`/`catch`,
  and services raise intent-revealing exceptions like `TaskNotFoundException` instead of
  returning null.
- **API resources** — serialisation is separate from the database schema, so columns can
  change without breaking the published contract.

### Conventions

- Strict types everywhere, `final` on classes not designed for extension, `readonly`
  on value objects and injected dependencies.
- The API is versioned under `/api/v1` so a future contract change is additive.
- Sortable and searchable columns are allow-listed on the `Task` model, so `sort_by`
  can never be pointed at an arbitrary column.

## Tests

27 tests / 98 assertions, run against an in-memory SQLite database.

- `tests/Feature/Api/V1/TaskApiTest.php` — all five endpoints, filtering, search,
  sorting, pagination, validation failures, 404s, 405s and the response envelopes.
- `tests/Unit/DataTransferObjects/TaskDataTest.php` — the partial-update semantics
  of `TaskData`.

```bash
php artisan test
```
