import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { glob } from 'glob';

function GetFilesArray(query) {
    return glob.sync(query);
  }

const pageJsFiles = GetFilesArray('resources/assets/js/*.js');
const LibsScssFiles = GetFilesArray('resources/assets//scss/!(_)*.scss');

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                 'resources/js/app.js',
                 ...pageJsFiles,
                 ...LibsScssFiles
                ],
            refresh: true,
        }),
    ],
});
