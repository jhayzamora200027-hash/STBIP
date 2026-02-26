const fs = require('fs');
const path = 'd:/for development/STBI-Portal-main/STBIP-home/resources/views/dashboard/main.blade.php';
let t = fs.readFileSync(path, 'utf8');
// add class to all total count headings
const replacements = {
    '<h1 style="font-size:2rem;">': '<h1 class="js-total-count" style="font-size:2rem;">'
};
Object.keys(replacements).forEach(k=>{
    t = t.split(k).join(replacements[k]);
});
fs.writeFileSync(path, t, 'utf8');
console.log('patched');
