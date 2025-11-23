// Dynamic Content Loader for Homepage
// Loads all dynamic content: rooms, jobs, blogs, events

class DynamicContentLoader {
    constructor() {
        this.rooms = [];
        this.jobs = [];
        this.blogPosts = [];
        this.events = [];
        this.featuredRooms = [];
        this.newsRooms = [];
        this.init();
    }

    async init() {
        try {
            await Promise.all([
                this.fetchRooms(),
                this.fetchJobs(),
                this.fetchBlogPosts(),
                this.fetchEvents()
            ]);
            this.populateAllCards();
        } catch (error) {
            console.error('Error loading dynamic content:', error);
            // Fallback to static content if fetch fails
        }
    }

    async fetchJobs() {
        try {
            const response = await fetch('fetch_jobs.php');
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            const data = await response.json();
            if (data.success && data.jobs) {
                this.jobs = data.jobs;
                console.log('Jobs fetched:', this.jobs.length);
            }
        } catch (error) {
            console.error('Error fetching jobs:', error);
        }
    }

    async fetchBlogPosts() {
        try {
            const response = await fetch('get_blog_posts.php');
            const data = await response.json();
            if (data.success && data.posts) {
                this.blogPosts = data.posts;
                console.log('Blog posts fetched:', this.blogPosts.length);
            }
        } catch (error) {
            console.error('Error fetching blog posts:', error);
        }
    }

    async fetchEvents() {
        try {
            const response = await fetch('get_whatsapp_groups.php');
            const data = await response.json();
            if (data.success && data.groups) {
                this.events = data.groups;
                console.log('Events fetched:', this.events.length);
            }
        } catch (error) {
            console.error('Error fetching events:', error);
        }
    }

    async fetchRooms() {
        try {
            const response = await fetch('fetch_rooms.php');
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            const data = await response.json();

            if (data.success && data.rooms) {
                console.log('All rooms from database:', data.rooms);

                // Prioritize boosted rooms for homepage
                const boostedRooms = data.rooms.filter(room => room.is_active_boost);
                const regularRooms = data.rooms.filter(room => !room.is_active_boost);

                console.log('Filtered boosted rooms:', boostedRooms);
                console.log('Filtered regular rooms:', regularRooms);

                // Show up to 2 boosted rooms, fill with regular if needed
                this.featuredRooms = [...boostedRooms.slice(0, 2)];
                console.log('Selected featured rooms:', this.featuredRooms);

                // For news section, show next boosted rooms or latest rooms
                const remainingBoosted = boostedRooms.slice(2);
                const latestRooms = [...remainingBoosted, ...regularRooms].slice(0, 2);
                this.newsRooms = latestRooms;
                console.log('News rooms for fallback:', this.newsRooms);
            }
        } catch (error) {
            console.error('Error fetching rooms:', error);
            this.featuredRooms = [];
            this.newsRooms = [];
        }
    }

    populateAllCards() {
        this.populateRoomCards();
        this.populateJobCards();
        this.populateBlogCards();
        this.populateEventCards();
    }

    populateJobCards() {
        // Update main content grid job card (Latest Job Posted)
        if (this.jobs.length > 0) {
            const jobCard = document.querySelector('.content-card:nth-child(3)'); // Third card in content-grid
            if (jobCard) {
                const latestJob = this.jobs[0];
                this.updateJobCard(jobCard, latestJob);
            }
        }

        // Update news section job cards
        const newsCards = document.querySelectorAll('.news-card');
        if (newsCards.length > 1 && this.jobs.length > 0) {
            // Second news card might be jobs if not showing second boosted room
            const jobNewsCard = newsCards[1];
            if (jobNewsCard && !jobNewsCard.querySelector('.boost')) { // Only update if not showing boosted room
                // Pick a random job instead of latest
                const randomIndex = Math.floor(Math.random() * this.jobs.length);
                this.updateJobCard(jobNewsCard, this.jobs[randomIndex]);
            }
        }
    }

    populateBlogCards() {
        // Update main content grid blog card (Latest Blog Article)
        if (this.blogPosts.length > 0) {
            const blogCard = document.querySelector('.content-card:nth-child(1)'); // First card in content-grid
            if (blogCard) {
                const latestPost = this.blogPosts[0];
                this.updateBlogCard(blogCard, latestPost);
            }
        }
    }

    populateEventCards() {
        // Update news section event cards
        const newsCards = document.querySelectorAll('.news-card');
        if (newsCards.length > 2 && this.events.length > 0) {
            const eventCard = newsCards[2]; // Third news card
            if (eventCard) {
                const latestEvent = this.events[0];
                this.updateEventCard(eventCard, latestEvent);
            }
        }
    }

    populateRoomCards() {
        // Ensure arrays are initialized
        if (!Array.isArray(this.featuredRooms)) {
            this.featuredRooms = [];
        }
        if (!Array.isArray(this.newsRooms)) {
            this.newsRooms = [];
        }

        // Update main content grid room card (Featured Room Boost)
        if (this.featuredRooms.length > 0) {
            const mainRoomCard = document.querySelector('.content-card:nth-child(2)'); // Second card in content-grid
            if (mainRoomCard) {
                this.updateRoomCard(mainRoomCard, this.featuredRooms[0], 'View Room', 'accommodation.html');
            }
        }

        // Update news section room cards
        console.log('Featured rooms count:', this.featuredRooms.length);
        console.log('News rooms count:', this.newsRooms.length);

        const newsCards = document.querySelectorAll('.news-card');

        // Put second boosted room in first news card, latest job in second, event in third
        if (this.featuredRooms.length > 1 && newsCards[0]) {
            console.log('Updating news card 0 with second boosted room');
            this.updateRoomCard(newsCards[0], this.featuredRooms[1], 'View Room', 'accommodation.html');
        } else if (newsCards[0] && this.newsRooms.length > 0) {
            console.log('Updating news card 0 with latest room');
            this.updateRoomCard(newsCards[0], this.newsRooms[0], 'View Room', 'accommodation.html');
        }
    }

    updateRoomCard(cardElement, roomData, buttonText, linkHref) {
        // Update image with actual room photo or keep existing
        const imageElement = cardElement.querySelector('.content-icon, .news-icon');
        if (imageElement && roomData.photo1) {
            // Use first room image from uploads directory
            imageElement.src = `uploads/${roomData.photo1}`;
            imageElement.onerror = function() {
                // Fallback to default image if room image fails to load
                this.src = 'images/1Copy.png';
            };
        }

        // Update title with actual room location and boost indicator
        const titleElement = cardElement.querySelector('.news-title, .content-title');
        if (titleElement) {
            const boostIndicator = roomData.is_active_boost ? ` <span class="boost">(Boost)</span>` : '';
            const roomTitle = roomData.title || `Room in ${roomData.city}`;
            titleElement.innerHTML = `${roomTitle}${boostIndicator}`;
        }

        // Update description with actual room details
        const descElement = cardElement.querySelector('.content-desc, .news-desc');
        if (descElement) {
            let description = '';

            // Add price if available
            if (roomData.price && roomData.price > 0) {
                description += `$${roomData.price}`;
                if (roomData.price_type) {
                    description += `/${roomData.price_type}`;
                }
                description += ' - ';
            }

            // Add room details
            if (roomData.room_type) {
                description += `${roomData.room_type}`;
            }
            if (roomData.furnished === 1 || roomData.furnished === '1') {
                description += ' (Furnished)';
            }

            // Add location info
            if (roomData.city && roomData.state) {
                description += ` in ${roomData.city}, ${roomData.state}`;
            } else if (roomData.city) {
                description += ` in ${roomData.city}`;
            }

            // Fallback to room description if basic info not available
            if (!description && roomData.description) {
                description = roomData.description;
            }

            // Truncate long descriptions
            if (description.length > 80) {
                description = description.substring(0, 80) + '...';
            }

            // Final fallback
            if (!description) {
                description = 'Room available for rent in Latin community';
            }

            descElement.textContent = description;
        }

        // Update button link
        const buttonElement = cardElement.querySelector('.content-btn, .news-btn');
        if (buttonElement) {
            buttonElement.textContent = buttonText;
            buttonElement.onclick = () => window.location.href = linkHref;
        }
    }

    updateJobCard(cardElement, jobData, buttonText = 'View Job', linkHref = 'jobs.html') {
        // Update title with actual job title
        const titleElement = cardElement.querySelector('.content-title, .news-title');
        if (titleElement) {
            titleElement.textContent = jobData.title || 'Latest Job Posted';
        }

        // Update description with job details
        const descElement = cardElement.querySelector('.content-desc, .news-desc');
        if (descElement) {
            let description = '';

            // Add company/location info
            if (jobData.company) {
                description += `${jobData.company}`;
            }
            if (jobData.location) {
                description += ` - ${jobData.location}`;
            }

            // Add salary if available
            if (jobData.salary) {
                description += ` - $${jobData.salary}`;
            }

            // Fallback to job description
            if (!description && jobData.description) {
                description = jobData.description.substring(0, 60) + '...';
            }

            // Final fallback
            if (!description) {
                description = 'View latest job opportunities';
            }

            descElement.textContent = description;
        }

        // Update button link
        const buttonElement = cardElement.querySelector('.content-btn, .news-btn');
        if (buttonElement) {
            buttonElement.textContent = buttonText;
            buttonElement.onclick = () => {
                if (jobData.id) {
                    // Link to specific job if jobs page supports it
                    window.location.href = `${linkHref}?id=${jobData.id}`;
                } else {
                    // Fallback to jobs page
                    window.location.href = linkHref;
                }
            };
        }
    }

    updateBlogCard(cardElement, blogData, buttonText = 'Read More', linkHref = 'blog.html') {
        // Update title with actual blog title
        const titleElement = cardElement.querySelector('.content-title, .news-title');
        if (titleElement) {
            titleElement.textContent = blogData.title || 'Latest Blog Article';
        }

        // Update description with blog excerpt
        const descElement = cardElement.querySelector('.content-desc, .news-desc');
        if (descElement) {
            let description = '';
            if (blogData.content) {
                // Strip HTML tags and truncate
                description = blogData.content.replace(/<[^>]*>/g, '').substring(0, 80) + '...';
            } else {
                description = 'Read our latest articles and guides';
            }
            descElement.textContent = description;
        }

        // Update image if available
        const imageElement = cardElement.querySelector('.content-icon, .news-icon');
        if (imageElement && blogData.image) {
            imageElement.src = `uploads/${blogData.image}`;
            imageElement.onerror = function() {
                this.src = 'images/laptop.png'; // Fallback to laptop icon
            };
        }

        // Update button link
        const buttonElement = cardElement.querySelector('.content-btn, .news-btn');
        if (buttonElement) {
            buttonElement.textContent = buttonText;
            buttonElement.onclick = () => {
                if (blogData.id) {
                    // Link to specific article if blog page supports it
                    window.location.href = `${linkHref}?id=${blogData.id}`;
                } else {
                    // Fallback to blog page
                    window.location.href = linkHref;
                }
            };
        }
    }

    updateEventCard(cardElement, eventData, buttonText = 'View Event', linkHref = 'partyandevents.html') {
        // Update title with actual event name
        const titleElement = cardElement.querySelector('.news-title');
        if (titleElement) {
            titleElement.innerHTML = `${eventData.name || 'Event Poster'} <span class="boost">(Boost)</span>`;
        }

        // Update description with event details
        const descElement = cardElement.querySelector('.news-desc');
        if (descElement) {
            const description = eventData.description ? eventData.description.substring(0, 60) + '...' :
                                `Join our WhatsApp group for ${eventData.city_name || 'local'} events`;
            descElement.textContent = description;
        }

        // Update image
        const imageElement = cardElement.querySelector('.news-icon');
        if (imageElement) {
            imageElement.src = 'images/event.png';
            imageElement.onerror = function() {
                this.src = 'images/1Copy.png'; // Fallback to 1Copy.png if event.png fails
            };
        }

        // Update button link
        const buttonElement = cardElement.querySelector('.news-btn');
        if (buttonElement) {
            buttonElement.textContent = buttonText;
            buttonElement.onclick = () => {
                window.location.href = 'partyandevents.html';
            };
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new DynamicContentLoader();
});
