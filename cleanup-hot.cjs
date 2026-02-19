const fs = require('fs');
const path = require('path');

const hotFile = path.join(__dirname, 'public', 'hot');

if (fs.existsSync(hotFile)) {
    fs.unlinkSync(hotFile);
    console.log('✓ Removed hot file');
} else {
    console.log('✓ No hot file to remove');
}
