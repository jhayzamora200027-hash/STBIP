const fs = require('fs');
const path = "D:/for development/STBI-Portal-main/STBIP-home/resources/views/dashboard/mainreports/_streportContent.blade.php";
const text = fs.readFileSync(path,'utf8');
const blocks = [...text.matchAll(/<script>([\s\S]*?)<\/script>/g)];
blocks.forEach((m,i)=>{
  const script = m[1];
  try{
    new Function(script);
  }catch(e){
  }
});
