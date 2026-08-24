import assert from 'node:assert/strict'; import fs from 'node:fs'; import test from 'node:test';
const css = fs.readFileSync(new URL('../assets/public.css', import.meta.url), 'utf8');
test('bulletins consume structural semantic colors and neutral viewer chrome', () => { for (const role of ['primary','surface','foreground','text-muted','border','on-primary']) assert.match(css, new RegExp(`--mc-color-${role}`)); assert.doesNotMatch(css, /color:\s*#fff\s*!important/); });
