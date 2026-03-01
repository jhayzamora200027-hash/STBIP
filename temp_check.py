import re, ast
path = r'D:\\for development\\STBI-Portal-main\\STBIP-home\\resources\\views\\dashboard\\mainreports\\_streportContent.blade.php'
text = open(path,'r',encoding='utf-8').read()
blocks = re.findall(r'<script>([\s\S]*?)</script>', text)
print('found', len(blocks), 'script blocks')
for idx, script in enumerate(blocks):
    print('--- block', idx, 'length', len(script))
    openb = script.count('{')
    closeb = script.count('}')
    openp = script.count('(')
    closep = script.count(')')
    print('  braces { }', openb, closeb, 'parens ( )', openp, closep)
    # show tail of script
    print(script[-200:])
