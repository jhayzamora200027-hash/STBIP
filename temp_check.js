const fs = require('fs');
const path = "D:/for development/STBI-Portal-main/STBIP-home/resources/views/dashboard/mainreports/_streportContent.blade.php";
const text = fs.readFileSync(path, 'utf8');
const blocks = [...text.matchAll(/<script>([\s\S]*?)<\/script>/g)];
console.log('found', blocks.length, 'script blocks');
blocks.forEach((m,i)=>{
  const script = m[1];
  console.log('block',i,'len',script.length);
  const openb = (script.match(/{/g)||[]).length;
  const closeb = (script.match(/}/g)||[]).length;
  const openp = (script.match(/\(/g)||[]).length;
  const closep = (script.match(/\)/g)||[]).length;
  console.log('braces',openb,closeb,'parens',openp,closep);
});
