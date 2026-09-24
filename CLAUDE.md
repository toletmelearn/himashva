<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.3. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record a rule with `record-rule` only when the user explicitly asks for one. Instructions for the work at hand are not rules, no matter how emphatic: "remove this typo", "use X here" are work to do, not rules to record. Never record a rule on your own initiative, as a byproduct of a change, or to summarize what you just did. When the user does ask, pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Use `record-rule` rather than your native memory or notes tool, because native memory is personal and session-scoped, while only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>


For EVERY task, follow this workflow before making conclusions or changes.

1. FIRST — READ PROJECT INSTRUCTIONS
   - Read CLAUDE.md.
   - Identify applicable project rules and restrictions.
   - Check whether the task is READ-ONLY, planning, implementation,
     debugging, refactoring, or database-related.

2. IDENTIFY TASK COMPLEXITY
   Determine whether the task is:

   SIMPLE
   - Small isolated change
   - Known file/class/method
   - No significant dependency uncertainty

   COMPLEX
   - Multiple modules/files involved
   - Unknown dependencies
   - Architecture/business-rule changes
   - Database/schema changes
   - Security/authentication/authorization
   - Large refactoring
   - New feature spanning multiple layers
   - Existing behaviour is unclear
   - Potential impact on unrelated modules

3. AUTOMATICALLY SELECT AGENCY AGENTS
   Do NOT require the user to specify agents manually.

   Select the relevant Agency Agent(s) automatically.

   Typical mapping:

   - Codebase Onboarding Engineer
     → unfamiliar code or dependency-heavy area

   - Backend Architect
     → architecture, business rules, service design

   - Senior Developer
     → approved implementation

   - Database Optimizer
     → database/schema/index/query/migration work

   - Security Engineer
     → authentication, authorization, permissions,
       tenant isolation, sensitive data

   - API Tester
     → API routes, validation, backend endpoint testing

   - Test Automation Engineer
     → browser/UI/end-to-end workflow testing

   - Code Reviewer
     → review before completion

   - Minimal Change Engineer
     → scope control and prevention of unrelated changes

   Only select agents relevant to the actual task.

4. USE THE CHEAPEST RELIABLE INSPECTION METHOD FIRST

   SIMPLE TASK
   → Direct source inspection

   UNKNOWN DEPENDENCY / COMPLEX CODEBASE RELATIONSHIP
   → Graphify

   ARCHITECTURE / STRUCTURAL / REDESIGN QUESTION
   → Archify

   BUSINESS-RULE QUESTION
   → Actual source code + existing tests

   DATABASE QUESTION
   → Migrations + models + relationships + constraints +
     existing queries/tests

   SECURITY QUESTION
   → Middleware + policies + gates + requests +
     controllers + routes + tests

   LARGE / CROSS-MODULE CHANGE
   → Graphify + Archify + relevant Agency Agents +
     direct source inspection

5. GRAPHIFY RULES

   When Graphify is appropriate:

   - Use graphify query for codebase questions.
   - Use graphify path for relationships/dependencies.
   - Use graphify explain for focused concepts.
   - Use graphify-out/wiki/index.md for broad navigation when useful.
   - Use GRAPH_REPORT.md only when broader architecture information
     is required or query/path/explain is insufficient.

   IMPORTANT:
   Graphify is structural evidence.

   Graphify MUST NOT replace inspection of the actual source code.

6. ARCHIFY RULES

   Use Archify when the task requires:

   - architecture understanding
   - system/module structure review
   - dependency visualization
   - redesign planning
   - significant architectural changes
   - communicating architecture clearly

   IMPORTANT:
   Archify output is architectural/structural evidence.

   It MUST NOT be treated as proof that the actual source code
   behaves in a particular way.

   Actual source code remains authoritative.

7. SOURCE CODE IS AUTHORITATIVE

   Always inspect the actual relevant:

   - Models
   - Controllers
   - Services
   - Repositories
   - Form Requests
   - Policies
   - Middleware
   - Routes
   - Migrations
   - Jobs
   - Events
   - Listeners
   - Blade views
   - API resources
   - Tests
   - Factories
   - Seeders

   Do not invent:

   - routes
   - controllers
   - models
   - services
   - tables
   - columns
   - permissions
   - APIs
   - business rules
   - workflows

8. CLASSIFY FINDINGS

   Clearly distinguish:

   VERIFIED FROM CODE
   → Directly confirmed by source code/tests.

   INFERRED FROM CODE STRUCTURE
   → Strong inference but not directly confirmed.

   UNKNOWN
   → Evidence is insufficient.

   NEEDS CONFIRMATION
   → Requires user/business clarification or additional evidence.

   Never present an inference as a verified fact.

9. BEFORE IMPLEMENTATION

   Before modifying anything:

   - Identify affected files.
   - Identify existing business rules.
   - Identify dependencies.
   - Identify tests covering the affected behaviour.
   - Identify possible cross-module impact.
   - Determine the smallest safe implementation.
   - Check whether the requested change conflicts with existing rules.

10. SCOPE CONTROL

   NEVER modify unrelated modules.

   If an unrelated issue is discovered:

   - Report it.
   - Do not fix it unless explicitly requested/approved.

   Prefer the smallest safe change that satisfies the task.

11. READ-ONLY TASK RULE

   If the task is marked READ-ONLY:

   - Do not modify files.
   - Do not create files.
   - Do not delete files.
   - Do not rename files.
   - Do not modify routes.
   - Do not modify models.
   - Do not modify controllers.
   - Do not modify database structure.
   - Do not run migrations.
   - Do not modify database data.
   - Do not implement features.

   Produce findings and recommendations only.

12. DATABASE SAFETY

   Never run:

   - migrate:fresh
   - db:wipe
   - migrate:refresh
   - database reset commands
   - destructive SQL
   - destructive seed operations

   unless explicitly authorized.

   No schema modification or migration should be performed
   without explicit approval when project rules require it.

13. TESTS ARE PART OF THE EVIDENCE

   Inspect existing tests relevant to the task.

   Use tests to:

   - verify existing behaviour
   - confirm business rules
   - identify regression risks
   - validate implementation
   - identify missing coverage

   Do not assume that undocumented behaviour is safe to change.

14. IMPLEMENTATION WORKFLOW

   For approved implementation:

   READ CLAUDE.md
        ↓
   IDENTIFY TASK COMPLEXITY
        ↓
   SELECT AGENCY AGENTS
        ↓
   INSPECT CODEBASE
        ↓
   GRAPHIFY IF NEEDED
        ↓
   ARCHIFY IF NEEDED
        ↓
   IDENTIFY BUSINESS RULES
        ↓
   IDENTIFY AFFECTED FILES
        ↓
   PLAN SMALLEST SAFE CHANGE
        ↓
   IMPLEMENT
        ↓
   RUN RELEVANT TESTS
        ↓
   CODE REVIEW
        ↓
   VERIFY NO UNRELATED CHANGES
        ↓
   REPORT RESULTS

15. AUDIT WORKFLOW

   For READ-ONLY audits:

   READ CLAUDE.md
        ↓
   SELECT AGENCY AGENTS
        ↓
   DETERMINE COMPLEXITY
        ↓
   GRAPHIFY IF NEEDED
        ↓
   ARCHIFY IF NEEDED
        ↓
   INSPECT ACTUAL SOURCE
        ↓
   INSPECT DATABASE STRUCTURE
        ↓
   INSPECT ROUTES / AUTHORIZATION
        ↓
   INSPECT TESTS
        ↓
   CLASSIFY FINDINGS
        ↓
   IDENTIFY RISKS
        ↓
   RECOMMEND NEXT STEPS
        ↓
   STOP — NO CODE CHANGES

16. REPORTING STANDARD

   Every substantial task should report:

   - Selected Agency Agents
   - Files inspected
   - Tools/evidence used
   - Verified findings
   - Inferences
   - Unknowns
   - Existing business rules
   - Risks
   - Tests run
   - Changes made, if any
   - Files changed
   - Unrelated areas intentionally left untouched

17. PROJECT-WIDE SAFETY PRINCIPLE

   Existing HelpingHand functionality is presumed intentional
   unless source evidence proves otherwise.

   Do not simplify, remove, replace, or redesign an existing
   mechanism merely because another mechanism appears cleaner.

   First establish:

   - what exists
   - what is authoritative
   - what depends on it
   - what tests protect it
   - what users currently rely on

   Then propose changes.

18. AFTER CODE CHANGES

   After modifying code:

   - Run relevant tests.
   - Review the diff.
   - Check for unintended files.
   - Check for unrelated modifications.
   - Re-run appropriate architectural/codebase inspection
     when the change materially affects structure.
   - If Graphify data becomes stale after code changes,
     update Graphify according to project rules.

19. CORE PRINCIPLE

   USE THE CHEAPEST RELIABLE METHOD FIRST.

   Do NOT automatically run every tool for every task.

   Direct source inspection is preferred for simple known tasks.

   Graphify and Archify are escalation tools for complexity,
   uncertainty, dependencies, and architecture.

   Agency Agents provide specialized reasoning/review.

   Actual source code and tests remain the final evidence.

                            USER TASK
                             │
                             ▼
                       Read CLAUDE.md
                             │
                             ▼
                  Identify task complexity
                             │
              ┌──────────────┴──────────────┐
              │                             │
           SIMPLE                         COMPLEX
              │                             │
              ▼                             ▼
      Direct source                  Graphify if needed
       inspection                         │
              │                           ▼
              │                     Archify if needed
              │                           │
              └──────────────┬────────────┘
                             ▼
                    Select Agency Agents
                             │
                             ▼
                     Actual Source Code
                             │
                             ▼
                    Business Rules / DB
                             │
                             ▼
                      Existing Tests
                             │
                             ▼
                 Plan / Implement / Audit
                             │
                             ▼
                      Code Review
                             │
                             ▼
                  Final Verification
                             │
                             ▼
                           REPORT
