const fs = require('fs');
const path = require('path');

function walk(dir) {
    let results = [];
    const list = fs.readdirSync(dir);
    list.forEach(file => {
        file = path.join(dir, file);
        const stat = fs.statSync(file);
        if (stat && stat.isDirectory()) {
            results = results.concat(walk(file));
        } else {
            if (file.endsWith('.ts') || file.endsWith('.tsx')) {
                results.push(file);
            }
        }
    });
    return results;
}

const files = walk('src');
files.forEach(file => {
    let content = fs.readFileSync(file, 'utf8');
    
    // 1. Replace double quotes "http://localhost:8000..."
    content = content.replace(/"http:\/\/(localhost|127\.0\.0\.1):8000([^"]*)"/g, '`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}$2`');
    
    // 2. Replace single quotes 'http://localhost:8000...'
    content = content.replace(/'http:\/\/(localhost|127\.0\.0\.1):8000([^']*)'/g, '`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}$2`');

    // 3. Replace backticks `http://localhost:8000...`
    content = content.replace(/`http:\/\/(localhost|127\.0\.0\.1):8000([^`]*)`/g, '`${process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000"}$2`');
    
    fs.writeFileSync(file, content);
});

console.log('Fixed URLs safely!');
