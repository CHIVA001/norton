<?php
/**
 * Common Footer Component
 */
?>
</div>
</main>

<!-- Footer -->
<footer class="bg-dark text-white mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5>About Us</h5>
                <p>Your trusted travel companion for unforgettable journeys around the world.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>index.php" class="text-white-50 text-decoration-none">Home</a>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>tours/destinations.php"
                            class="text-white-50 text-decoration-none">Destinations</a></li>
                    <li><a href="<?php echo BASE_URL; ?>tours/tour-list.php"
                            class="text-white-50 text-decoration-none">Tours</a></li>
                    <li><a href="<?php echo BASE_URL; ?>tours/tour-search.php"
                            class="text-white-50 text-decoration-none">Search Tours</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Contact Us</h5>
                <p class="text-white-50">
                    <i class="fas fa-phone"></i> +1 (555) 123-4567<br>
                    <i class="fas fa-envelope"></i> info@travelagency.com<br>
                    <i class="fas fa-map-marker-alt"></i> 123 Travel St, World City, WC 12345
                </p>
            </div>
        </div>
        <hr class="bg-secondary">
        <div class="text-center text-white-50">
            <p>&copy; 2026 Travel Agency. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (optional, for easier DOM manipulation) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Custom JS -->
<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>

</html>