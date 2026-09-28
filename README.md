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
cp .env.example .env         # Windows: copy .env.example .env
php artisan key:generate
php artisan migrate --seed   # creates database/database.sqlite when prompted;
                             # --seed is optional and adds 17 sample tasks
php artisan serve
```

SQLite is the default connection, so there is nothing else to configure. The API is
then served at `http://localhost:8000/api/v1/tasks`.

Run the suite with `php artisan test` and the formatter with `vendor/bin/pint`.

## Architecture

A request passes through the layers in one direction:

```
Route -> FormRequest -> Controller -> Service -> Repository -> Eloquent
```

| Layer           | Location                      | Responsibility                                |
| --------------- | ----------------------------- | --------------------------------------------- |
| Form requests   | `app/Http/Requests/Api/V1`    | Validate input, then return a DTO             |
| Controller      | `app/Http/Controllers/Api/V1` | Turn an HTTP call into a service call         |
| Service         | `app/Services`                | Business rules for the task use cases         |
| Repository      | `app/Repositories`            | Database access, behind an interface          |
| DTOs            | `app/DataTransferObjects`     | Immutable payload and filter objects          |
| Resource        | `app/Http/Resources`          | Public JSON shape of a task                   |
| Response traits | `app/Http/Responses/Concerns` | Success and error envelopes                   |
| Exceptions      | `app/Exceptions`              | Domain failures mapped to HTTP status codes   |

### Patterns

**Repository.** `TaskRepositoryInterface` is bound to `TaskRepository` in
`RepositoryServiceProvider`. `TaskService` depends on the interface, so storage can be
swapped or faked without changing business logic. Shared CRUD lives in an abstract
`BaseRepository`.

**Service layer.** Business rules sit outside the controller, which keeps the use cases
callable from a command or queue job as well as from HTTP.

**DTOs.** `TaskData` records which attributes the client sent, so a `PATCH` updates one
field without blanking the rest, while `description` can still be cleared by sending an
explicit `null`. `TaskFilters` describes how a listing is filtered and sorted.

**Response traits.** `SendsSuccessResponses` and `SendsErrorResponses` build the two
envelopes and `InteractsWithApiResponses` composes both. Every response goes through
them, so the shape stays consistent across endpoints.

**Centralised exceptions.** `ApiExceptionRenderer`, registered in `bootstrap/app.php`,
converts validation failures, missing records, disallowed methods and unexpected errors
into the error envelope. The controllers contain no `try`/`catch`, and the service raises
`TaskNotFoundException` rather than returning null.

**API resources.** `TaskResource` keeps the JSON contract independent of the database
schema.

### Conventions

- `declare(strict_types=1)` throughout, `final` where extension is not intended, and
  `readonly` on value objects and injected dependencies.
- Routes are versioned under `/api/v1`.
- Searchable and sortable columns are allow-listed on the `Task` model, so `sort_by`
  cannot be pointed at an arbitrary column.

## Tests

77 tests against an in-memory SQLite database.

| Suite                                              | Covers                                                    |
| -------------------------------------------------- | --------------------------------------------------------- |
| `tests/Feature/Api/V1/TaskApiTest.php`             | All five endpoints end to end, including every error case |
| `tests/Unit/Services/TaskServiceTest.php`          | Use cases against a mocked repository                     |
| `tests/Unit/DataTransferObjects/TaskDataTest.php`  | Partial-update semantics                                  |
| `tests/Unit/DataTransferObjects/TaskFiltersTest.php` | Filter parsing and defaults                             |
| `tests/Unit/Http/Responses/ApiResponsesTest.php`   | Both response envelopes                                   |
| `tests/Unit/Exceptions/ApiExceptionRendererTest.php` | Exception to status-code mapping                        |
| `tests/Unit/Models/TaskTest.php`                   | Casts, defaults and mass-assignment rules                 |

```bash
php artisan test
```
