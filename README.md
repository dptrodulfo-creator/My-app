# RunSheet: Delivery Driver Management (CC106 Lab 2)

A small Laravel app. A **dispatcher** sends delivery tasks (item + destination) to **drivers**.
Each driver sees new tasks, with a badge in the top bar, and sends a "Confirm received" back.
The dispatcher monitors the drivers, the tasks, and the accounts.

No starter kit and no auth package: the login, email verification and password reset code is
written by hand in `app/Http/Controllers/Auth`.

This folder is **only the app layer**. You create the project with `laravel new`, then drop these files in.

---

## 1. Set up

Needs PHP 8.3+ (Laravel 13) or 8.2+ (Laravel 12), plus Composer and the Laravel installer.

1. Create the project and pick **No starter kit** (None), any testing framework, and **SQLite**:
   ```
   laravel new runsheet
   cd runsheet
   ```
2. Copy these folders from this package into the project, merging with what is there and replacing files when asked:
   `app`, `config`, `database`, `public`, `resources`, `routes`, `tests`.
   Four default files get replaced on purpose: `app/Models/User.php`, `routes/web.php`,
   `database/seeders/DatabaseSeeder.php` and `tests/Feature/ExampleTest.php`.
3. Open `.env` and **edit the existing lines** to match `env-snippet.txt` (APP_NAME, SESSION_DRIVER, MAIL_*).
   Do not paste a second copy at the bottom: when a name appears twice, the first one wins, so you would
   stay on `MAIL_MAILER=log`. Do the same in `.env.example`, but with placeholders only, never a real password.
4. Fill in your Gmail details (section 3 below).
5. Build the database and example accounts, then start the app:
   ```
   php artisan migrate --seed
   php artisan serve
   ```
   Open http://127.0.0.1:8000. After any change to `.env`, stop and restart `php artisan serve`.
6. Run the tests: `php artisan test`

---

## 2. Example accounts (created by `php artisan migrate --seed`)

| Role | Email | Password |
|---|---|---|
| Dispatcher (restricted role) | `dispatcher@runsheet.test` | `Dispatch@123` |
| Driver (default role) | `driver@runsheet.test` | `Driver@123` |

Both start with a confirmed email so you can sign in straight away. The example driver also has five sample tasks.
The `.test` addresses can't receive mail, so to try the **email** features, register a new account with a
real inbox. That new account is a driver, because nobody can pick the dispatcher role on the register form.

---

## 3. Gmail (Features 4 and 5 must send real email)

1. On the Gmail account that will send the mail, turn on **2-Step Verification** (Google Account, Security).
2. Create an **App Password**: https://myaccount.google.com/apppasswords. Name it "RunSheet".
   Google shows a 16-letter code. Copy it without spaces.
3. In `.env`:
   ```
   MAIL_MAILER=smtp
   MAIL_SCHEME=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your.address@gmail.com
   MAIL_PASSWORD=the-16-letter-app-password
   MAIL_FROM_ADDRESS="your.address@gmail.com"
   MAIL_FROM_NAME="${APP_NAME}"
   ```
   Your normal Gmail password will be refused. It has to be the App Password.
4. Register with a real email. The confirmation link arrives from your Gmail address (check Spam too).
   Then try "Forgot your password?" on the sign-in page.

---

## 4. Design card answers (for the Google Sheet)

Fill the sheet **before** anything else: no two groups can have the same answers. If a row is taken,
change the wording or the names (section 6 says where each one lives).

| Item | Our answer |
|---|---|
| System name and what it does | **RunSheet**: a dispatch board where dispatchers send delivery tasks to drivers and drivers confirm they received them. |
| Restricted role | `dispatcher` |
| Default role for new accounts | `driver` |
| Choice remembered during the session | **Delivery zone**: Riverside (default), Hillcrest, Old Harbor, Airport Strip |
| Flash: registered | Welcome aboard, {name}! We emailed you a link. Click it to unlock RunSheet. |
| Flash: logged in | Signed in. Good to see you, {name}. |
| Flash: choice changed | Zone switched to {zone}. |
| Flash: logged out | Signed out. Drive safe! |
| 403 message | Hold up. Your account isn’t cleared for this page. |

---

## 5. What each role can do

**Driver:** sign in, see delivery tasks with item information and destination (**My Run Sheet**), see a
badge and a banner for new tasks, and press **Confirm received**.

**Dispatcher:** sign in, then use
- **Dispatch Desk**: totals, who is still to confirm, the latest confirmations
- **Task Board**: send a new task to a driver and monitor every task (shown for the picked zone)
- **Driver Roster**: each driver, their waiting and received counts, and their tasks
- **Accounts**: every account, last sign-in, email confirmed or not, and **Put on hold / Reinstate**
  (a driver on hold can't sign in, and is signed out on their next click)

---

## 6. Where each lab feature lives (study this: anyone can be asked about any part)

| Feature | Read these |
|---|---|
| 1. Login | `Auth/RegisterController`, `Auth/LoginController`, the `auth` group in `routes/web.php`, `resources/views/auth/` |
| 2. Session | `ZoneController` saves `session('zone')`. `app/Support/Zone.php` reads it and falls back to the default. `config/runsheet.php` holds the list. The dropdown is in `layouts/app.blade.php`. `RunSheetController` and `Desk/TaskBoardController` filter by it. Flash messages are `->with('status', ...)` in the controllers, shown in the layout. |
| 3. Roles | `User.php` (role constants, `$fillable` without role). `RegisterController` forces the default role. `DatabaseSeeder` creates the dispatcher. `EnsureRole` plus the `desk` group in `routes/web.php`. `errors/403.blade.php`. |
| 4. Email verification | `User implements MustVerifyEmail`. `event(new Registered(...))` in `RegisterController`. `Auth/EmailVerificationController`. The `verified` middleware in `routes/web.php`. `Notifications/ConfirmEmail`. |
| 5. Password reset | `Auth/PasswordResetController`, `Notifications/ChooseNewPassword`, the `forgot-password` and `reset-password` views. Tokens live in the `password_reset_tokens` table that Laravel's default migration creates. |

### If we change X, what breaks?

- Add `'role'` to `$fillable` **and** switch registration to `User::create($request->all())`: anyone could register as a dispatcher by adding a hidden `role` field. Today the controller reads only name, email and password, and sets the role itself.
- Remove `verified` from the route group: unconfirmed accounts can use the app (Feature 4 fails).
- Remove `$request->session()->invalidate()` from logout: the zone pick survives sign-out.
- Set `SESSION_DRIVER=database`: it still works, but the pick is then stored in the `sessions` table, not just "the session".
- Leave `MAIL_MAILER=log`: emails are only written to `storage/logs/laravel.log`. Nothing reaches Gmail.
- Rename the routes `login`, `verification.notice` or `password.reset`: Laravel's own `auth` and `verified` middleware and the reset email look for those names, and break.
- Take `EnsureRole` off the `desk` group: a driver can open the desk by typing the address.
- Remove `EnsureAccountIsActive` from the route group: a driver put on hold stays signed in until their session ends.

---

## 7. Submitting

Zip the project folder **without** `vendor/`, `node_modules/` and `.env`.
Check that `.env.example` has no real password in it.

---

## 8. If something goes wrong

- `Failed to authenticate on SMTP server` / `535`: wrong App Password, 2-Step Verification is off, or the
  username isn't the full Gmail address. Re-create the App Password and paste it with no spaces.
- `Connection timed out` to smtp.gmail.com: the network blocks port 587. Try `MAIL_PORT=465` and `MAIL_SCHEME=smtps`.
- Email "sent" but nothing arrives and `laravel.log` has the text of the email: `MAIL_MAILER` is still `log`
  (see the duplicate-lines warning in step 3).
- Changed `.env` and nothing happened: restart `php artisan serve`, then `php artisan config:clear`.
- `Route [...] not defined` or `Class ... not found`: check the folders were copied into the project, then run
  `composer dump-autoload` and `php artisan route:list`.
- Start over with fresh example data: `php artisan migrate:fresh --seed`.
