# Task Scheduler
This assignment is a PHP-based task management system where users can add tasks to a common list and subscribe to receive hourly email reminders for pending tasks.

---

## 🚀 Your Task

Your objective is to implement the functionality in the **src/** directory while following these rules:

✅ **DO NOT** change function names or modify the file structure.  
✅ **DO NOT** modify anything outside the **src/** folder. You can add additional files if required inside **src** folder.
✅ **DO NOT** use a database; use text files for storage.  
✅ Implement all required functions in `functions.php`.  
✅ Implement the main interface in `index.php`.  
✅ Implement email verification and unsubscribe functionality.

---

## 📝 Submission Steps [ Non adherence to this will cause disqualification ]
1. **Clone** the repository to your local machine.  
2. **Create a new branch** from the `main` branch. **Do not** push code directly to `main`.  
3. **Implement** the required features inside the `src/` directory.  
4. **Push** your code to your **branch** (not `main`).  
5. **Raise a Pull Request (PR) only once** against the `main` branch when all your code is finalized.  
   - **Do not raise multiple PRs.**  
   - **Do not add multiple commits to a PR after submission.**  
6. **Failure to follow these instructions will result in disqualification.**  
7. **Wait** for your submission to be reviewed. Do not merge the PR.

---
## ⚠️ Important Notes

All form elements should always be visible on the page and should not be conditionally rendered. This ensures the assignment can be tested properly at the appropriate steps.

Please ensure that if the base repository shows the original template repo, update it so that your repo's main branch is set as the base branch.

**Recommended PHP version: 8.3**

---

## 📌 Features to Implement

### 1️⃣ **Task Management**

- Add new tasks to the common list
- Duplicate tasks should not be added.
- Mark tasks as complete/incomplete
- Delete tasks
- Store tasks in `tasks.txt`

### 2️⃣ **Email Subscription System**

- Users can subscribe with their email
- Email verification process:
  - System generates a unique 6-digit verification code
  - Sends verification email with activation link
  - Link contains email and verification code
  - User clicks link to verify subscription
  - System moves email from pending to verified subscribers
- Store subscribers in `subscribers.txt`
- Store pending verifications in `pending_subscriptions.txt`

### 3️⃣ **Reminder System**

- CRON job runs every hour
- Sends emails to verified subscribers
- Only includes pending tasks in reminders
- Includes unsubscribe link in emails
- Unsubscribe process:
  - Every email includes an unsubscribe link
  - Link contains encoded email address
  - One-click unsubscribe removes email from subscribers

---

## 📜 File Details & Function Stubs

You **must** implement the following functions in `functions.php`:

```php
function addTask($task_name) {
    // Add a new task to the list
}

function getAllTasks() {
    // Get all tasks from tasks.txt
}

function markTaskAsCompleted($task_id, $is_completed) {
    // Mark/unmark a task as complete
}

function deleteTask($task_id) {
    // Delete a task from the list
}

function generateVerificationCode() {
    // Generate a 6-digit verification code
}

function subscribeEmail($email) {
    // Add email to pending subscriptions and send verification
}

function verifySubscription($email, $code) {
    // Verify email subscription
}

function unsubscribeEmail($email) {
    // Remove email from subscribers list
}

function sendTaskReminders() {
    // Sends task reminders to all subscribers
 	// Internally calls  sendTaskEmail() for each subscriber
}

function sendTaskEmail( $email, $pending_tasks ) {
	// Sends a task reminder email to a subscriber with pending tasks.
}
```

## 📁 File Structure

- `functions.php` (Core functions)
- `index.php` (Main interface)
- `verify.php` (Email verification handler)
- `unsubscribe.php` (Unsubscribe handler)
- `cron.php` (Reminder sender)
- `setup_cron.sh` (CRON job setup)
- `tasks.txt` (Task storage)
- `subscribers.txt` (Verified subscribers)
- `pending_subscriptions.txt` (Pending verifications)

## 🔄 CRON Job Implementation

📌 You must implement a **CRON job** that runs `cron.php` every 1 hour.  
📌 **Do not just write instructions**—provide an actual **setup_cron.sh** script inside `src/`.  
📌 **Your script should automatically configure the CRON job on execution.**

---

### 🛠 Required Files

- **`setup_cron.sh`** (Must configure the CRON job)
- **`cron.php`** (Must handle sending GitHub updates via email)

---

### 🚀 How It Should Work

- The `setup_cron.sh` script should register a **CRON job** that executes `cron.php` every 1 hour.
- The CRON job **must be automatically added** when the script runs.
- The `cron.php` file should actually **fetch pending tasks** and **send emails** to subscribed users.

---

## 📩 Email Handling

✅ The email content must be in **HTML format** (not JSON).  
✅ Use **PHP's `mail()` function** for sending emails.  
✅ Each email should include an **unsubscribe link**.  
✅ Store subscribers email in `subscribers.txt` (**Do not use a database**).
✅ Store pending verifications in `pending_subscriptions.txt` (**Do not use a database**).
✅ Each email should include an **unsubscribe link**.

---

## ❌ Disqualification Criteria

🚫 **Hardcoding** verification codes.  
🚫 **Using a database** (use `subscribers.txt`).  
🚫 **Modifying anything outside** the `src/` directory.  
🚫 **Changing function names**.  
🚫 **Not implementing a working CRON job**.  
🚫 **Not formatting emails as HTML**.
🚫 Using 3rd party libraries, only pure PHP is allowed.

---

## 📌 Input & Button Formatting Guidelines

### 📝 Task Management Inputs & Button:

- Add task input must have `name="task-name"` and `id="task-name"`
- Add task button must have `id="add-task"`

#### ✅ Example:

```html
<input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
<button type="submit" id="add-task">Add Task</button>
```
- Task list must have `class="task-list"`.
- Task item in that list must have `class="task-item`
- Task item must have have a checkbox `<input type="checkbox" class="task-status" >` so user can mark it done.
- Once task item is completed add class `completed`. `<li class="task-item completed">`  
- Task item should have a delete action with `class="delete-task"`.

#### ✅ Example:

```html
<ul class="tasks-list">
	<li class="task-item">
		<input type="checkbox" class="task-status">
		<button class="delete-task">Delete</button>
	</li>	
</ul>
```

### 📧 Email Input & Submission Button:

- The email input field must have `name="email"`.
- The submit button must have `id="submit-email"`.

#### ✅ Example:

```html
<input type="email" name="email" required />
<button id="submit-email">Submit</button>
```

---

## 📩 Email Content Guidelines

#### ✅ Verification Email:

- **Subject:** `Verify subscription to Task Planner`
- **Body Format:**

```html
<p>Click the link below to verify your subscription to Task Planner:</p>
';
<p><a id="verification-link" href="{verification_link}">Verify Subscription</a></p>
```

- Sender: no-reply@example.com

---

### 📩 Email Content Guidelines

⚠️ Note: The Subject and Body of the email must strictly follow the formats below, including the exact HTML structure.

#### ✅ Task Reminder Email:

- **Subject:** `Task Planner - Pending Tasks Reminder`
- **Body Format:**

```html
<h2>Pending Tasks Reminder</h2>
<p>Here are the current pending tasks:</p>
<ul>
	<li>Task 1</li>
	<li>Task 2</li>
</ul>
<p><a id="unsubscribe-link" href="{unsubscribe_link}">Unsubscribe from notifications</a></p>
```

---
## 📊 Data Storage Format

All data must be stored in JSON format in the text files.

### Tasks Format (`tasks.txt`):

Tasks must be stored as a JSON array of objects with the following schema:

```json
[
	{
		"id": "unique_task_id",
		"name": "Task Name",
		"completed": false
	},
	{
		"id": "another_task_id",
		"name": "Another Task",
		"completed": true
	}
]
```

### Subscribers Format (`subscribers.txt`):

Subscribers must be stored as a JSON array of email addresses:

```json
["user1@example.com", "user2@example.com"]
```

### Pending Subscriptions Format (`pending_subscriptions.txt`):

Pending subscriptions must be stored as a JSON object with emails as keys:

```json
{
	"user1@example.com": {
		"code": "123456",
		"timestamp": 1717694230
	},
	"user2@example.com": {
		"code": "654321",
		"timestamp": 1717694245
	}
}
```

⚠️ **Important**: Ensuring your data follows these exact JSON schemas is critical for proper validation.
