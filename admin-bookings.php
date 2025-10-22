<?php
include 'config.php';
session_start();

if(!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// Handle booking actions
if(isset($_POST['action'])) {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];
    
    // Get booking details for notification
    $booking_sql = "SELECT b.*, u.name as user_name, u.email, u.department, u.contact_number, h.name as hall_name, h.capacity 
                   FROM bookings b 
                   JOIN users u ON b.user_id = u.id 
                   JOIN halls h ON b.hall_id = h.id 
                   WHERE b.id = '$booking_id'";
    $booking_result = $conn->query($booking_sql);
    $booking_data = $booking_result->fetch_assoc();
    
    $notification_message = "";
    $notification_type = "";
    
    if($action == 'approve') {
        $sql = "UPDATE bookings SET status='confirmed' WHERE id='$booking_id'";
        $notification_message = "Booking #$booking_id has been APPROVED for {$booking_data['user_name']}";
        $notification_type = 'success';
        
        // Send approval notification
        sendBookingEmail($booking_data, 'approved', $booking_data['email'], $booking_data['user_name']);
        
    } else if($action == 'reject') {
        $sql = "UPDATE bookings SET status='rejected' WHERE id='$booking_id'";
        $notification_message = "Booking #$booking_id has been REJECTED for {$booking_data['user_name']}";
        $notification_type = 'warning';
        
        // Send rejection notification
        sendBookingEmail($booking_data, 'rejected', $booking_data['email'], $booking_data['user_name']);
        
    } else if($action == 'delete') {
        $sql = "DELETE FROM bookings WHERE id='$booking_id'";
        $notification_message = "Booking #$booking_id has been DELETED for {$booking_data['user_name']}";
        $notification_type = 'error';
        
        // Send cancellation notification
        sendBookingEmail($booking_data, 'cancelled', $booking_data['email'], $booking_data['user_name']);
    }
    
    if($conn->query($sql) === TRUE) {
        // Store notification in session
        $_SESSION['notification'] = [
            'message' => $notification_message,
            'type' => $notification_type,
            'action' => $action,
            'booking_id' => $booking_id,
            'user_name' => $booking_data['user_name']
        ];
        
        // Redirect to avoid form resubmission
        header("Location: admin-bookings.php");
        exit();
    } else {
        $_SESSION['notification'] = [
            'message' => "Error processing request",
            'type' => 'error',
            'action' => 'error'
        ];
        header("Location: admin-bookings.php");
        exit();
    }
}

// Show notification if exists
if(isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    unset($_SESSION['notification']);
}

// Build filter query
$filter_sql = "SELECT b.*, u.name as user_name, u.email, u.department, u.college_type, u.contact_number, 
               h.name as hall_name, h.capacity as hall_capacity
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN halls h ON b.hall_id = h.id WHERE 1=1";

// Add filter conditions
if(isset($_GET['status']) && $_GET['status'] != 'all') {
    $status = $_GET['status'];
    $filter_sql .= " AND b.status = '$status'";
}

// Add search filter
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $filter_sql .= " AND (u.name LIKE '%$search%' OR h.name LIKE '%$search%' OR b.purpose LIKE '%$search%' OR b.chief_guest LIKE '%$search%')";
}

$filter_sql .= " ORDER BY b.created_at DESC, b.booking_date DESC";

$result = $conn->query($filter_sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Bookings - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-pending { background-color: #fff3cd; }
        .status-confirmed { background-color: #d1edff; }
        .status-rejected { background-color: #f8d7da; }
        .college-type-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .badge-sf {
            background-color: #dc3545;
            color: white;
        }
        .badge-aided {
            background-color: #198754;
            color: white;
        }
        .action-buttons {
            min-width: 200px;
        }
        .filter-card {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        }
        .scale-badge-small { background-color: #28a745; }
        .scale-badge-medium { background-color: #ffc107; color: black; }
        .scale-badge-large { background-color: #dc3545; }
        .progress {
            height: 8px;
            margin-top: 5px;
        }
        .utilization-low { background-color: #28a745; }
        .utilization-medium { background-color: #ffc107; }
        .utilization-high { background-color: #dc3545; }
        .computer-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            min-width: 350px;
            background: white;
            border-left: 5px solid;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideInRight 0.5s ease-out;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .notification-success { border-color: #28a745; }
        .notification-warning { border-color: #ffc107; }
        .notification-error { border-color: #dc3545; }
        .notification-info { border-color: #17a2b8; }
        
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .notification-header {
            padding: 12px 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
            font-weight: 600;
        }
        
        .notification-body {
            padding: 15px;
        }
        
        .notification-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 12px;
        }
        
        .icon-success { background: #28a745; color: white; }
        .icon-warning { background: #ffc107; color: black; }
        .icon-error { background: #dc3545; color: white; }
        .icon-info { background: #17a2b8; color: white; }
        .contact-badge {
            background: #17a2b8;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <!-- Computer Style Notification -->
    <?php if(isset($notification)): ?>
    <div class="computer-notification notification-<?php echo $notification['type']; ?>">
        <div class="notification-header d-flex justify-content-between align-items-center">
            <div>
                <span class="notification-icon icon-<?php echo $notification['type']; ?>">
                    <?php if($notification['action'] == 'approve'): ?>
                        <i class="fas fa-check"></i>
                    <?php elseif($notification['action'] == 'reject'): ?>
                        <i class="fas fa-times"></i>
                    <?php elseif($notification['action'] == 'delete'): ?>
                        <i class="fas fa-trash"></i>
                    <?php else: ?>
                        <i class="fas fa-info"></i>
                    <?php endif; ?>
                </span>
                <span>
                    <?php 
                    $titles = [
                        'approve' => 'Booking Approved',
                        'reject' => 'Booking Rejected', 
                        'delete' => 'Booking Deleted',
                        'error' => 'System Notification'
                    ];
                    echo $titles[$notification['action']] ?? 'Notification';
                    ?>
                </span>
            </div>
            <small class="text-muted"><?php echo date('H:i:s'); ?></small>
        </div>
        <div class="notification-body">
            <p class="mb-2"><?php echo $notification['message']; ?></p>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">Admin Panel</small>
                <button type="button" class="btn-close" onclick="this.parentElement.parentElement.parentElement.style.display='none'"></button>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-hide notification after 6 seconds
        setTimeout(function() {
            const notification = document.querySelector('.computer-notification');
            if (notification) {
                notification.style.animation = 'slideInRight 0.5s ease-out reverse';
                setTimeout(() => notification.style.display = 'none', 500);
            }
        }, 6000);
    </script>
    <?php endif; ?>

    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="admin-dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <div class="navbar-text">
                <a href="admin-dashboard.php" class="text-white">Dashboard</a> | 
                <a href="admin-logout.php" class="text-white">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2><i class="fas fa-tasks"></i> Manage Bookings</h2>

        <!-- Filter Options -->
        <div class="card mt-4 filter-card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-filter"></i> Filter & Search Bookings</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="admin-bookings.php">
                    <div class="row">
                        <div class="col-md-4">
                            <label><strong>Status Filter:</strong></label>
                            <div>
                                <button type="submit" name="status" value="all" class="btn btn-outline-primary btn-sm">All</button>
                                <button type="submit" name="status" value="pending" class="btn btn-outline-warning btn-sm">Pending</button>
                                <button type="submit" name="status" value="confirmed" class="btn btn-outline-success btn-sm">Confirmed</button>
                                <button type="submit" name="status" value="rejected" class="btn btn-outline-danger btn-sm">Rejected</button>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label><strong>Search:</strong></label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search by user name, hall, or chief guest..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                                <a href="admin-bookings.php" class="btn btn-secondary"><i class="fas fa-times"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label><strong>Current Filter:</strong></label>
                            <div class="mt-2">
                                <?php
                                $current_filter = "All Bookings";
                                if(isset($_GET['status']) && $_GET['status'] != 'all') {
                                    $current_filter = ucfirst($_GET['status']) . " Bookings";
                                }
                                if(isset($_GET['search']) && !empty($_GET['search'])) {
                                    $current_filter .= " (Search: " . $_GET['search'] . ")";
                                }
                                echo "<span class='badge bg-dark'>$current_filter</span>";
                                ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list"></i> Booking Requests</h5>
                <span class="badge bg-light text-dark">
                    Total: <?php echo $result->num_rows; ?> bookings
                </span>
            </div>
            <div class="card-body">
                <?php if($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>User Details</th>
                                <th>College Type</th>
                                <th>Hall & Capacity</th>
                                <th>Date & Time</th>
                                <th>Event Scale</th>
                                <th>Chief Guest</th>
                                <th>Utilization</th>
                                <th>Status</th>
                                <th>Booking Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): 
                                // Calculate utilization percentage
                                $utilization = ($row['student_strength'] / $row['hall_capacity']) * 100;
                                $utilization_class = $utilization < 50 ? 'utilization-low' : ($utilization < 80 ? 'utilization-medium' : 'utilization-high');
                            ?>
                            <tr class="status-<?php echo $row['status']; ?>">
                                <td><strong><?php echo $row['id']; ?></strong></td>
                                <td>
                                    <strong><?php echo $row['user_name']; ?></strong><br>
                                    <small class="text-muted"><?php echo $row['email']; ?></small><br>
                                    <small>
                                        <?php echo $row['department']; ?>
                                        <?php if(!empty($row['contact_number'])): ?>
                                        <span class="contact-badge">
                                            📞 <?php echo $row['contact_number']; ?>
                                        </span>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="college-type-badge badge-<?php echo strtolower($row['college_type']); ?>">
                                        <?php echo $row['college_type']; ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo $row['hall_name']; ?></strong><br>
                                    <small class="text-muted">Capacity: <?php echo $row['hall_capacity']; ?></small>
                                </td>
                                <td>
                                    <strong><?php echo date('M j, Y', strtotime($row['booking_date'])); ?></strong><br>
                                    <?php echo date('h:i A', strtotime($row['start_time'])); ?> - 
                                    <?php echo date('h:i A', strtotime($row['end_time'])); ?>
                                </td>
                                <td>
                                    <?php
                                    $scale_badges = [
                                        'Small' => 'scale-badge-small',
                                        'Medium' => 'scale-badge-medium', 
                                        'Large' => 'scale-badge-large'
                                    ];
                                    $scale_class = isset($scale_badges[$row['event_scale']]) ? $scale_badges[$row['event_scale']] : 'scale-badge-small';
                                    ?>
                                    <span class="badge <?php echo $scale_class; ?>">
                                        <?php echo isset($row['event_scale']) ? $row['event_scale'] : 'Small'; ?>
                                    </span>
                                    <br>
                                    <small class="text-muted"><?php echo $row['purpose']; ?></small>
                                </td>
                                <td>
                                    <?php if(!empty($row['chief_guest'])): ?>
                                        <strong><?php echo $row['chief_guest']; ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo round($utilization, 1); ?>%</small>
                                    <div class="progress">
                                        <div class="progress-bar <?php echo $utilization_class; ?>" 
                                             style="width: <?php echo $utilization; ?>%">
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo $row['student_strength']; ?>/<?php echo $row['hall_capacity']; ?>
                                    </small>
                                </td>
                                <td>
                                    <?php 
                                    $status_badge = [
                                        'pending' => 'warning',
                                        'confirmed' => 'success',
                                        'rejected' => 'danger'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $status_badge[$row['status']]; ?>">
                                        <i class="fas fa-<?php echo $row['status'] == 'confirmed' ? 'check' : ($row['status'] == 'rejected' ? 'times' : 'clock'); ?>"></i>
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('M j, h:i A', strtotime($row['created_at'])); ?>
                                    </small>
                                </td>
                                <td class="action-buttons">
                                    <div class="btn-group-vertical btn-group-sm">
                                        <?php if($row['status'] == 'pending'): ?>
                                        <form method="POST" class="d-inline mb-1">
                                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm w-100">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline mb-1">
                                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="reject" class="btn btn-warning btn-sm w-100">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        
                                        <!-- Delete button for all statuses -->
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm w-100" 
                                                    onclick="return confirm('Are you sure you want to delete this booking? Email notification will be sent to user.')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No booking requests found for current filter.
                    <a href="admin-bookings.php" class="alert-link">Show all bookings</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>