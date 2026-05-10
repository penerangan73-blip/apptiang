const fs = require('fs');

function replaceFile(path) {
    if (!fs.existsSync(path)) return;
    let file = fs.readFileSync(path, 'utf8');

    file = file.replace(/text-slate-950/g, 'text-slate-100');
    file = file.replace(/text-slate-600/g, 'text-slate-300');
    file = file.replace(/text-slate-500/g, 'text-slate-400');
    file = file.replace(/bg-slate-50/g, 'bg-white/5');
    file = file.replace(/border-slate-100/g, 'border-white/10');
    file = file.replace(/border-slate-200/g, 'border-white/10');
    file = file.replace(/divide-slate-200/g, 'divide-white/10');
    file = file.replace(/divide-slate-100/g, 'divide-white/5');

    fs.writeFileSync(path, file);
}

replaceFile('resources/views/partials/poles-table.blade.php');
replaceFile('resources/views/partials/search-filter.blade.php');
replaceFile('resources/views/dashboard.blade.php');
replaceFile('resources/views/partials/dialogs.blade.php');

console.log('Done replacing colors in partials and dashboard');
