<?php
require_once 'functions.php';

// Handle Add Task
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['task-name'])) {
		addTask(trim($_POST['task-name']));
	}

	// Handle Checkbox Completion
	if (isset($_POST['toggle-task'])) {
		$task_id = $_POST['toggle-task'];
		$is_completed = isset($_POST['status']) ? true : false;
		markTaskAsCompleted($task_id, $is_completed);
	}

	// Handle Task Delete
	if (isset($_POST['delete-task'])) {
		deleteTask($_POST['delete-task']);
	}

	// Handle Email Subscribe
	if (isset($_POST['email'])) {
		subscribeEmail(trim($_POST['email']));
	}

	// Handle Email Verification
	if (isset($_POST['verify-email']) && isset($_POST['verify-code'])) {
		verifySubscription(trim($_POST['verify-email']), trim($_POST['verify-code']));
	}

	// Handle Unsubscribe
	if (isset($_POST['unsubscribe'])) {
		unsubscribeEmail(trim($_POST['unsubscribe']));
	}

	header("Location: " . $_SERVER['PHP_SELF']);
	exit();
}

$tasks = getAllTasks();
?>

<!DOCTYPE html>
<html>
<head>
	<title>Task Scheduler</title>
</head>
<body>

	<!-- Add Task Form -->
	<form method="POST" action="">
		<input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
		<button type="submit" id="add-task">Add Task</button>
	</form>

	<!-- Tasks List -->
	<ul id="tasks-list">
		<?php foreach ($tasks as $task): ?>
			<li class="task-item">
				<form method="POST" style="display:inline;">
					<input type="hidden" name="toggle-task" value="<?= htmlspecialchars($task['id']) ?>">
					<input type="checkbox" class="task-status" name="status" <?= $task['completed'] ? 'checked' : '' ?> onchange="this.form.submit()">
				</form>

				<?= htmlspecialchars($task['name']) ?>

				<form method="POST" style="display:inline;">
					<input type="hidden" name="delete-task" value="<?= htmlspecialchars($task['id']) ?>">
					<button class="delete-task" type="submit">Delete</button>
				</form>
			</li>
		<?php endforeach; ?>
	</ul>

	<!-- Subscription Form -->
	<h3>Subscribe to Task Reminders</h3>
	<form method="POST" action="">
		<input type="email" name="email" placeholder="Enter your email" required>
		<button type="submit" id="submit-email">Subscribe</button>
	</form>

	<!-- Email Verification Form -->
	<h3>Verify Subscription</h3>
	<form method="POST" action="">
		<input type="email" name="verify-email" placeholder="Enter your email" required>
		<input type="text" name="verify-code" placeholder="Enter verification code" required>
		<button type="submit">Verify</button>
	</form>

	<!-- Unsubscribe Form -->
	<h3>Unsubscribe</h3>
	<form method="POST" action="">
		<input type="email" name="unsubscribe" placeholder="Your email to unsubscribe" required>
		<button type="submit">Unsubscribe</button>
	</form>

</body>
</html>
