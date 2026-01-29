<?php

/**
 * Adds a new task to the task list
 * 
 * @param string $task_name The name of the task to add.
 * @return bool True on success, false on failure.
 */
function addTask( string $task_name ): bool {
	$file  = __DIR__ . '/tasks.txt';
	// TODO: Implement this function
	$tasks = getAllTasks();

    foreach ($tasks as $task) {
        if (strtolower($task['name']) === strtolower($task_name)) {
            return false; // Duplicate
        }
    }

    $tasks[] = [
        'id' => uniqid(),
        'name' => $task_name,
        'completed' => false
    ];

    return file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Retrieves all tasks from the tasks.txt file
 * 
 * @return array Array of tasks. -- Format [ id, name, completed ]
 */
function getAllTasks(): array {
    $file = __DIR__ . '/tasks.txt';

    if (!file_exists($file) || filesize($file) === 0) {
        return []; // Return empty array if file doesn't exist or is empty
    }

    $content = file_get_contents($file);
    $tasks = json_decode($content, true);

    if (!is_array($tasks)) {
        return []; // If decoding failed, return empty array instead of null
    }

    return $tasks;
}

/**
 * Marks a task as completed or uncompleted
 * 
 * @param string  $task_id The ID of the task to mark.
 * @param bool $is_completed True to mark as completed, false to mark as uncompleted.
 * @return bool True on success, false on failure
 */
function markTaskAsCompleted( string $task_id, bool $is_completed ): bool {
	$file  = __DIR__ . '/tasks.txt';
	// TODO: Implement this function
	$tasks = getAllTasks();

    foreach ($tasks as &$task) {
        if ($task['id'] === $task_id) {
            $task['completed'] = $is_completed;
            return file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT)) !== false;
        }
    }
    return false;
}

/**
 * Deletes a task from the task list
 * 
 * @param string $task_id The ID of the task to delete.
 * @return bool True on success, false on failure.
 */
function deleteTask( string $task_id ): bool {
	$file  = __DIR__ . '/tasks.txt';
	// TODO: Implement this function
	$tasks = getAllTasks();
    $filtered = array_filter($tasks, fn($task) => $task['id'] !== $task_id);

    return file_put_contents($file, json_encode(array_values($filtered), JSON_PRETTY_PRINT)) !== false;
}

/**
 * Generates a 6-digit verification code
 * 
 * @return string The generated verification code.
 */
function generateVerificationCode(): string {
	// TODO: Implement this function
	return str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Subscribe an email address to task notifications.
 *
 * Generates a verification code, stores the pending subscription,
 * and sends a verification email to the subscriber.
 *
 * @param string $email The email address to subscribe.
 * @return bool True if verification email sent successfully, false otherwise.
 */
function subscribeEmail(string $email): void {
    $file = __DIR__ . '/pending_subscriptions.txt';
    $pending = [];

    if (file_exists($file)) {
        $pendingJson = file_get_contents($file);
        $pending = json_decode($pendingJson, true);

        if (!is_array($pending)) {
            $pending = [];
        }
    }

    $code = generateVerificationCode();
    $pending[] = ['email' => $email, 'code' => $code];
    file_put_contents($file, json_encode($pending, JSON_PRETTY_PRINT));

    // Compose the verification email
    $verificationLink = "http://localhost:8000/verify.php?email=" . urlencode($email) . "&code=$code";
    $message = "<p>Click below to verify your email:</p><a id='verification-link' href='$verificationLink'>Verify Subscription</a>";

    // For local test, write to file instead of sending
    file_put_contents(__DIR__ . '/email.txt', "To: $email\nSubject: Verify subscription to Task Planner\nMessage:\n$message\n\n", FILE_APPEND);

    // Optional redirect to avoid resubmission
    header("Location: index.php?subscribed=1");
    exit;
}


/**
 * Verifies an email subscription
 * 
 * @param string $email The email address to verify.
 * @param string $code The verification code.
 * @return bool True on success, false on failure.
 */
function verifySubscription(string $email, string $code): bool {
    $file = __DIR__ . '/pending_subscriptions.txt';

    if (!file_exists($file)) {
        return false;
    }

    $pendingJson = file_get_contents($file);
    $pending = json_decode($pendingJson, true);

    // Defensive check
    if (!is_array($pending)) {
        $pending = [];
    }

    // Find the matching entry
    foreach ($pending as $index => $entry) {
        if (strtolower($entry['email']) === strtolower($email) && $entry['code'] === $code) {

            // Move email to verified list
            $subscribedFile = __DIR__ . '/subscribed_emails.txt';
            $subscribed = [];

            if (file_exists($subscribedFile)) {
                $subscribedJson = file_get_contents($subscribedFile);
                $subscribed = json_decode($subscribedJson, true);

                if (!is_array($subscribed)) {
                    $subscribed = [];
                }
            }

            if (!in_array(strtolower($email), array_map('strtolower', $subscribed))) {
                $subscribed[] = $email;
            }

            file_put_contents($subscribedFile, json_encode($subscribed, JSON_PRETTY_PRINT));

            // Remove from pending
            unset($pending[$index]);
            file_put_contents($file, json_encode(array_values($pending), JSON_PRETTY_PRINT));

            return true;
        }
    }

    return false;
}

/**
 * Unsubscribes an email from the subscribers list
 * 
 * @param string $email The email address to unsubscribe.
 * @return bool True on success, false on failure.
 */
function unsubscribeEmail(string $email): bool {
    $file = __DIR__ . '/subscribers.txt';
    
    if (!file_exists($file)) {
        return false;
    }

    $subscribers = json_decode(file_get_contents($file), true);

    if (!is_array($subscribers)) {
        return false;
    }

    $new_subs = array_filter($subscribers, fn($sub) => $sub !== $email);

    return file_put_contents($file, json_encode(array_values($new_subs), JSON_PRETTY_PRINT)) !== false;
}

/**
 * Sends task reminders to all subscribers
 * Internally calls  sendTaskEmail() for each subscriber
 */
function sendTaskReminders(): void {
    $subscribers_file = __DIR__ . '/subscribers.txt';
    $subscribers = file_exists($subscribers_file) ? json_decode(file_get_contents($subscribers_file), true) : [];

    $tasks = getAllTasks();
    $pending_tasks = array_filter($tasks, fn($task) => !$task['completed']);

    foreach ($subscribers as $email) {
        sendTaskEmail($email, $pending_tasks);
    }
}

/**
 * Sends a task reminder email to a subscriber with pending tasks.
 *
 * @param string $email The email address of the subscriber.
 * @param array $pending_tasks Array of pending tasks to include in the email.
 * @return bool True if email was sent successfully, false otherwise.
 */
function sendTaskEmail( string $email, array $pending_tasks ): bool {
	$subject = 'Task Planner - Pending Tasks Reminder';
	// TODO: Implement this function
	$taskList = "<ul>";
    foreach ($pending_tasks as $task) {
        $taskList .= "<li>{$task['name']}</li>";
    }
    $taskList .= "</ul>";

    $unsubscribe = "http://localhost:8000/unsubscribe.php?email=" . urlencode($email);

    $message = "<h2>Pending Tasks Reminder</h2><p>Here are your pending tasks:</p>$taskList<p><a id='unsubscribe-link' href='$unsubscribe'>Unsubscribe</a></p>";
    $headers = "Content-type: text/html\r\nFrom: no-reply@task-planner.com";

    return mail($email, $subject, $message, $headers);
}
