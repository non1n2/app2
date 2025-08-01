import Echo from 'laravel-echo';

import Pusher from 'pusher-js';

window.Pusher = Pusher; // Crucial: Echo needs Pusher on the window

window.Echo = new Echo({ // Crucial: Assign to window.Echo
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    encrypted: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }
});