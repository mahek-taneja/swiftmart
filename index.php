<?php
require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/data/products.php';

/**
 * Helpers
 */
function url_with_encoded_spaces(string $path): string {
    // Encode spaces only; keep slashes
    return preg_replace('/\s/', '%20', $path);
}

function build_asset(string $relative): string {
    return url_with_encoded_spaces(build_path($relative));
}

function render_stars(float $rating): string {
    // Bootstrap Icons stars: full, half, empty
    $full = floor($rating);
    $frac = $rating - $full;
    $half = ($frac >= 0.25 && $frac < 0.75) ? 1 : 0;
    if ($frac >= 0.75) { $full++; $half = 0; }
    $empty = 5 - $full - $half;

    return str_repeat('<i class="bi bi-star-fill" aria-hidden="true"></i>', (int)$full)
         . str_repeat('<i class="bi bi-star-half" aria-hidden="true"></i>', (int)$half)
         . str_repeat('<i class="bi bi-star" aria-hidden="true"></i>', (int)$empty);
}

// Data
$categories = [
    ['name' => 'Watch',        'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&h=200&fit=crop', 'count' => '17 Products'],
    ['name' => 'Fashionista',  'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=200&h=200&fit=crop', 'count' => '6 Products'],
    ['name' => 'Ethnic Wear',  'image' => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=200&h=200&fit=crop', 'count' => '4 Products'],
    ['name' => 'Goggles',      'image' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=200&h=200&fit=crop', 'count' => '10 Products'],
    ['name' => 'Tote Bag',     'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=200&h=200&fit=crop', 'count' => '4 Products'],
    ['name' => 'Shoes',        'image' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=200&h=200&fit=crop', 'count' => '5 Products'],
    ['name' => 'Electronics',  'image' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=200&h=200&fit=crop', 'count' => '23 Products'],
    ['name' => 'Home Decor',   'image' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=200&h=200&fit=crop', 'count' => '12 Products'],
    ['name' => 'Sports',       'image' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=200&h=200&fit=crop', 'count' => '8 Products'],
    ['name' => 'Books',        'image' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=200&h=200&fit=crop', 'count' => '15 Products'],
];
// Duplicate once for a seamless loop
$categoriesLoop = array_merge($categories, $categories);

$featuredRestaurants = [
    [
        'name' => 'Poultry Palace',
        'rating' => 3.9,
        'description' => 'Chicken quesadilla, avocado, grilled chicken...',
        'location' => 'New Jersey',
        'distance' => '3.2 km',
        'delivery_time' => '25 min',
        'image' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&h=400&fit=crop',
        'deal' => '50% OFF',
        'badge' => 'Exclusive'
    ],
    [
        'name' => 'Ribeye Junction',
        'rating' => 3.2,
        'description' => 'Chicken quesadilla, avocado, grilled chicken...',
        'location' => 'California',
        'distance' => '1 km',
        'delivery_time' => '10 min',
        'image' => 'https://images.unsplash.com/photo-1551782450-a2132b4ba21d?w=800&h=400&fit=crop',
        'deal' => '50% OFF',
        'badge' => null
    ],
    [
        'name' => "The Grill Master's Cafe",
        'rating' => 4.3,
        'description' => 'Bread, Eggs, Butter, Fries...',
        'location' => 'New York',
        'distance' => '5 km',
        'delivery_time' => '40 min',
        'image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&h=400&fit=crop',
        'deal' => null,
        'badge' => null
    ],
    [
        'name' => 'Cozy Cuppa Cafe',
        'rating' => 3.6,
        'description' => 'Cheesecake, waffles, Cakes...',
        'location' => 'Dallas',
        'distance' => '4 km',
        'delivery_time' => '30 min',
        'image' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=800&h=400&fit=crop',
        'deal' => null,
        'badge' => null
    ],
    [
        'name' => 'Mocha Magic Cafe',
        'rating' => 3.2,
        'description' => 'Chinese, Momos, Dumplings...',
        'location' => 'Seattle',
        'distance' => '1 km',
        'delivery_time' => '8 min',
        'image' => 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?w=800&h=400&fit=crop',
        'deal' => null,
        'badge' => null
    ],
    [
        'name' => 'Latte Lounge',
        'rating' => 3.6,
        'description' => 'Chicken fingers, Chicken goujons...',
        'location' => 'Atlanta',
        'distance' => '3 km',
        'delivery_time' => '25 min',
        'image' => 'https://images.unsplash.com/photo-1551218808-94e220e084d2?w=800&h=400&fit=crop',
        'deal' => '50% OFF',
        'badge' => 'Best Seller'
    ]
];

// Assets (ensure poster exists for better LCP)
$heroVideo  = build_asset('/assets/img/hero%20banner.mp4');   // encodes space
$heroPoster = build_asset('/assets/img/hero-poster.jpg');     // add this image to your project
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php render_head('swiftmart – Home'); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- If Bootstrap Icons are not in render_head(), include this: 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"> -->
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <!-- Hero (SINGLE video only) -->
    <section class="hero-video-section" aria-label="Discover restaurants near you">
        <video class="hero-video" autoplay muted loop playsinline preload="metadata"
            poster="<?= htmlspecialchars($heroPoster) ?>">
            <source src="<?= htmlspecialchars($heroVideo) ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="hero-overlay" aria-hidden="true"></div>
        <div class="hero-content">
            <p class="hero-subtitle">Searching the most delicious dishes in your area...</p>
            <form class="hero-search" action="<?= htmlspecialchars(build_path('/customer/listings.php')) ?>"
                method="get" role="search" aria-label="Restaurant search">
                <label class="visually-hidden" for="hero-search-input">Search</label>
                <input id="hero-search-input" type="search" name="q"
                    placeholder="Search for food, restaurants, or cuisines"
                    aria-label="Search for food, restaurants, or cuisines" required>
                <button type="submit">
                    <i class="bi bi-search me-2" aria-hidden="true"></i>
                    <span>Search</span>
                </button>
            </form>
            <noscript>
                <img src="<?= htmlspecialchars($heroPoster) ?>" alt="Delicious food collage" width="1200" height="600"
                    style="max-width:100%;height:auto;">
            </noscript>
        </div>
    </section>

    <main class="container py-4" id="main-content">

        <!-- CTA Card (replacing the old second video section) -->
        <section class="my-5" aria-labelledby="experience-title">
            <div class="cta-card">
                <div class="cta-gradient"></div>
                <div class="cta-content">
                    <h2 id="experience-title" class="cta-title">Experience SwiftMart</h2>
                    <p class="cta-subtitle">Your ultimate destination for everything you need</p>
                    <a href="<?= htmlspecialchars(build_path('/customer/listings.php')) ?>" class="cta-btn">Explore
                        Now</a>
                </div>
            </div>
        </section>

        <!-- Categories (continuous horizontal marquee) -->
        <section class="my-5" aria-labelledby="categories-title">
            <div class="section-header">
                <h2 id="categories-title" class="section-title">Shop by Category</h2>
                <p class="section-subtitle">Discover our curated collection of products</p>
            </div>

            <div class="categories-marquee" aria-live="polite">
                <div class="marquee-track">
                    <?php foreach ($categoriesLoop as $category):
                    $href = build_path('/customer/listings.php?category=') . rawurlencode($category['name']);
                ?>
                    <a href="<?= htmlspecialchars($href) ?>" class="category-pill"
                        title="<?= htmlspecialchars($category['name']) ?>">
                        <span class="pill-thumb">
                            <img src="<?= htmlspecialchars($category['image']) ?>" alt="" loading="lazy"
                                decoding="async" width="48" height="48" />
                        </span>
                        <span class="pill-text">
                            <span class="pill-name"><?= htmlspecialchars($category['name']) ?></span>
                            <span class="pill-count"><?= htmlspecialchars($category['count']) ?></span>
                        </span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Featured Restaurants -->
        <section class="my-5" aria-labelledby="featured-title">
            <div class="section-header">
                <h2 id="featured-title" class="section-title">Featured Stores</h2>
                <p class="section-subtitle">Find nearby popular restaurants with fast delivery</p>
            </div>

            <div class="row g-4">
                <?php foreach ($featuredRestaurants as $restaurant): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <article class="restaurant-card position-relative"
                        aria-label="<?= htmlspecialchars($restaurant['name']) ?>">
                        <?php if (!empty($restaurant['deal'])): ?>
                        <div class="deal-badge" aria-label="Deal">
                            <div class="deal-percentage"><?= htmlspecialchars($restaurant['deal']) ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($restaurant['badge'])): ?>
                        <div class="position-absolute top-0 start-0 m-3">
                            <span
                                class="badge bg-warning text-dark"><?= htmlspecialchars($restaurant['badge']) ?></span>
                        </div>
                        <?php endif; ?>

                        <img src="<?= htmlspecialchars($restaurant['image']) ?>" class="restaurant-image"
                            alt="<?= htmlspecialchars($restaurant['name']) ?>" loading="lazy" decoding="async"
                            width="400" height="200">
                        <div class="restaurant-info">
                            <h3 class="restaurant-name"><?= htmlspecialchars($restaurant['name']) ?></h3>
                            <div class="restaurant-rating"
                                aria-label="Rating <?= number_format($restaurant['rating'],1) ?> out of 5">
                                <span class="rating-stars"><?= render_stars((float)$restaurant['rating']) ?></span>
                                <span class="rating-number"><?= number_format($restaurant['rating'],1) ?></span>
                            </div>
                            <p class="restaurant-description"><?= htmlspecialchars($restaurant['description']) ?></p>
                            <div class="restaurant-meta">
                                <div class="delivery-time">
                                    <i class="bi bi-clock" aria-hidden="true"></i>
                                    <span><?= htmlspecialchars($restaurant['delivery_time']) ?></span>
                                </div>
                                <div class="distance">
                                    <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                    <span><?= htmlspecialchars($restaurant['distance']) ?></span>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4 text-center">
                <a class="btn btn-outline-primary btn-lg" href="<?= htmlspecialchars(build_path('/stores.php')) ?>">View
                    All Restaurants</a>
            </div>
        </section>

        <!-- Today's Deal -->
        <section class="my-5" aria-labelledby="deals-title">
            <div class="section-header">
                <h2 id="deals-title" class="section-title">Today's Deal</h2>
                <p class="section-subtitle">Take a benefit from our latest offers</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-percent-circle-fill text-primary" style="font-size: 3rem;"
                                aria-hidden="true"></i>
                        </div>
                        <h4 class="card-title text-primary fw-bold">50% OFF</h4>
                        <p class="card-text text-muted">On your first order from any restaurant</p>
                        <a href="<?= htmlspecialchars(build_path('/customer/listings.php')) ?>"
                            class="btn btn-primary">Order Now</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-truck text-success" style="font-size: 3rem;" aria-hidden="true"></i>
                        </div>
                        <h4 class="card-title text-success fw-bold">Free Delivery</h4>
                        <p class="card-text text-muted">On orders above ₹2,000 from partner restaurants</p>
                        <a href="<?= htmlspecialchars(build_path('/customer/listings.php')) ?>"
                            class="btn btn-success">Shop Now</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-gift text-warning" style="font-size: 3rem;" aria-hidden="true"></i>
                        </div>
                        <h4 class="card-title text-warning fw-bold">Special Offers</h4>
                        <p class="card-text text-muted">Exclusive deals from top-rated restaurants</p>
                        <a href="<?= htmlspecialchars(build_path('/stores.php')) ?>" class="btn btn-warning">Explore</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>