const fs = require('fs');
const path = require('path');

const replacements = [
  [/bg-white\/[0-9]+/g, (match) => match.replace('white', 'background')],
  [/bg-white/g, 'bg-card'],
  [/border-slate-200/g, 'border-border'],
  [/border-slate-800\/[0-9]+/g, 'border-border'],
  [/border-slate-800/g, 'border-border'],
  [/bg-slate-50/g, 'bg-background'],
  [/bg-slate-100/g, 'bg-secondary'],
  [/bg-slate-950/g, 'bg-card'],
  [/bg-slate-900/g, 'bg-card'],
  [/text-slate-950/g, 'text-foreground'],
  [/text-slate-900/g, 'text-foreground'],
  [/text-slate-800/g, 'text-foreground'],
  [/text-slate-700/g, 'text-muted-foreground'],
  [/text-slate-600/g, 'text-muted-foreground'],
  [/text-slate-500/g, 'text-muted-foreground'],
  [/text-cyan-700/g, 'text-primary'],
  [/text-cyan-600/g, 'text-primary'],
  [/bg-cyan-50/g, 'bg-primary/10'],
  [/bg-cyan-100/g, 'bg-primary/20'],
  [/ring-cyan-200/g, 'ring-primary/30'],
  [/bg-cyan-400/g, 'bg-primary text-primary-foreground'],
  [/text-cyan-300/g, 'text-primary'],
  [/text-cyan-200/g, 'text-primary'],
  [/text-cyan-400/g, 'text-primary'],
  [/border-cyan-300/g, 'border-primary'],
];

function processDir(dir) {
  const files = fs.readdirSync(dir);
  for (const file of files) {
    const fullPath = path.join(dir, file);
    if (fs.statSync(fullPath).isDirectory()) {
      processDir(fullPath);
    } else if (fullPath.endsWith('.tsx') || fullPath.endsWith('.ts')) {
      let content = fs.readFileSync(fullPath, 'utf8');
      let newContent = content;
      for (const [regex, replacement] of replacements) {
        newContent = newContent.replace(regex, replacement);
      }
      if (content !== newContent) {
        fs.writeFileSync(fullPath, newContent);
        console.log(`Updated ${fullPath}`);
      }
    }
  }
}

processDir(path.join(__dirname, 'src'));
