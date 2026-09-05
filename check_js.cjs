const fs = require('fs');
const content = fs.readFileSync('/home/crab/Works/Projects/LMS2025/resources/views/student/courses/show.blade.php', 'utf8');
const regex = /<script.*?>([\s\S]*?)<\/script>/gi;
let match;
while ((match = regex.exec(content)) !== null) {
  let scriptContent = match[1];
  // More careful replacement
  scriptContent = scriptContent.replace(/\{\{.*?\}\}/g, 'null');
  scriptContent = scriptContent.replace(/@json\(.*?\)/g, 'null');
  scriptContent = scriptContent.replace(/@if.*?|@else.*?|@endif/g, '');
  try {
    new Function(scriptContent);
    console.log("Valid!");
  } catch (e) {
    console.log("Syntax error:", e.message);
  }
}
