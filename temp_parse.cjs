const fs = require('fs');
const path = "resources/views/dashboard/mainreports/_streportContent.blade.php";
try {
  const text = fs.readFileSync(path,'utf8');
  const blocks = [...text.matchAll(/<script>([\s\S]*?)<\/script>/g)];
  blocks.forEach((m,i)=>{
    const script = m[1];
    // Save extracted scripts for manual review instead of executing them
    try {
      const out = `temp_extracted_script_${i}.js`;
      fs.writeFileSync(out, script, 'utf8');
      console.log('Wrote', out);
    } catch (e) {
      console.error('Failed to write extracted script', e && e.message);
    }
  });
} catch (e) {
  console.error('Failed to read file', path, e && e.message);
}
