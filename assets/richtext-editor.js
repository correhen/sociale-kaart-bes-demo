(function(){
  'use strict';

  function escapeHtml(value){
    return String(value ?? '').replace(/[&<>"']/g, character => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    })[character]);
  }

  function renderInline(text){
    const formatted = value => escapeHtml(value)
      .replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>')
      .replace(/(^|[^*])\*([^*\n]+)\*/g, '$1<em>$2</em>');
    const linkPattern = /\[([^\]\n]+)\]\(([^)\s]+)\)/g;
    let html = '';
    let lastIndex = 0;
    let match;
    while((match = linkPattern.exec(text)) !== null) {
      html += formatted(text.slice(lastIndex, match.index));
      const url = match[2].trim();
      html += /^(?:https?:\/\/|mailto:)/i.test(url)
        ? `<a href="${escapeHtml(url)}"${/^https?:\/\//i.test(url) ? ' target="_blank"' : ''} rel="noopener noreferrer">${formatted(match[1])}</a>`
        : formatted(match[0]);
      lastIndex = linkPattern.lastIndex;
    }
    return html + formatted(text.slice(lastIndex));
  }

  function renderPreviewHtml(text){
    const source = String(text ?? '').replace(/\r\n?/g, '\n');
    if(!source.trim()) return '<p class="richtext-preview-empty">Nog geen tekst om te tonen.</p>';
    const html = [];
    let paragraph = [];
    let listType = '';
    let listItems = [];
    const flushParagraph = () => {
      if(paragraph.length) html.push(`<p>${paragraph.splice(0).map(renderInline).join('<br>')}</p>`);
    };
    const flushList = () => {
      if(!listType) return;
      html.push(`<${listType}>${listItems.map(item => `<li>${renderInline(item)}</li>`).join('')}</${listType}>`);
      listType = '';
      listItems = [];
    };
    source.split('\n').forEach(line => {
      const bullet = line.match(/^\s*(?:-|•|·|â€¢|Â·)\s+(.+)$/);
      const numbered = line.match(/^\s*\d+[.)]\s+(.+)$/);
      if(bullet || numbered) {
        flushParagraph();
        const nextListType = bullet ? 'ul' : 'ol';
        if(listType && listType !== nextListType) flushList();
        listType = nextListType;
        listItems.push((bullet || numbered)[1].trim());
      } else if(!line.trim()) {
        flushParagraph();
        flushList();
      } else {
        flushList();
        paragraph.push(line.trim());
      }
    });
    flushParagraph();
    flushList();
    return html.join('');
  }

  function replaceSelection(textarea, replacement, selectionStart, selectionEnd){
    textarea.setRangeText(replacement, selectionStart, selectionEnd, 'select');
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
    textarea.focus();
  }

  function wrapSelection(textarea, before, after, placeholder){
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected = textarea.value.slice(start, end) || placeholder;
    replaceSelection(textarea, before + selected + after, start, end);
    textarea.setSelectionRange(start + before.length, start + before.length + selected.length);
  }

  function prefixLines(textarea, ordered){
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const lineStart = textarea.value.lastIndexOf('\n', Math.max(0, start - 1)) + 1;
    const nextBreak = textarea.value.indexOf('\n', end);
    const lineEnd = nextBreak === -1 ? textarea.value.length : nextBreak;
    const lines = textarea.value.slice(lineStart, lineEnd).split('\n');
    const replacement = lines.map((line, index) => {
      const content = line.replace(/^\s*(?:[-•·]|â€¢|Â·|\d+[.)])\s+/, '');
      return `${ordered ? `${index + 1}.` : '-'} ${content}`;
    }).join('\n');
    replaceSelection(textarea, replacement, lineStart, lineEnd);
  }

  function insertLink(textarea){
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const label = textarea.value.slice(start, end) || 'linktekst';
    const url = window.prompt('Voer een URL in die begint met https://, http:// of mailto:', 'https://');
    if(url === null) return;
    if(!/^(?:https?:\/\/|mailto:)/i.test(url.trim())) {
      window.alert('Gebruik een URL die begint met https://, http:// of mailto:.');
      return;
    }
    replaceSelection(textarea, `[${label}](${url.trim()})`, start, end);
  }

  function createButton(label, title, action){
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'richtext-toolbar__button';
    button.textContent = label;
    button.title = title;
    button.addEventListener('click', action);
    return button;
  }

  function enhanceTextarea(textarea, options = {}){
    if(!textarea || textarea.dataset.richtextReady === 'true' || textarea.disabled || textarea.readOnly) return null;
    textarea.dataset.richtextReady = 'true';
    const editor = document.createElement('div');
    editor.className = 'richtext-editor';
    textarea.parentNode.insertBefore(editor, textarea);
    editor.appendChild(textarea);

    const toolbar = document.createElement('div');
    toolbar.className = 'richtext-toolbar';
    toolbar.setAttribute('role', 'toolbar');
    toolbar.setAttribute('aria-label', 'Tekstopmaak');
    toolbar.append(
      createButton('Vet', 'Maak de selectie vet', () => wrapSelection(textarea, '**', '**', 'tekst')),
      createButton('Cursief', 'Maak de selectie cursief', () => wrapSelection(textarea, '*', '*', 'tekst')),
      createButton('• Lijst', 'Maak een bulletlijst', () => prefixLines(textarea, false)),
      createButton('1. Lijst', 'Maak een genummerde lijst', () => prefixLines(textarea, true)),
      createButton('Link', 'Voeg een veilige link toe', () => insertLink(textarea))
    );
    editor.insertBefore(toolbar, textarea);

    const renderPreview = options.preview === false
      ? null
      : (typeof options.renderPreview === 'function' ? options.renderPreview : renderPreviewHtml);
    let preview = null;
    if(renderPreview) {
      const previewButton = createButton('Voorbeeld', 'Toon of verberg het voorbeeld', () => {
        const isHidden = preview.hidden;
        preview.hidden = !isHidden;
        previewButton.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
        if(isHidden) preview.innerHTML = renderPreview(textarea.value);
      });
      previewButton.classList.add('richtext-preview-toggle');
      previewButton.setAttribute('aria-expanded', 'false');
      toolbar.appendChild(previewButton);

      preview = document.createElement('div');
      preview.className = 'richtext-preview rich-text';
      preview.hidden = true;
      editor.appendChild(preview);
      textarea.addEventListener('input', () => {
        if(!preview.hidden) preview.innerHTML = renderPreview(textarea.value);
      });
    }

    const help = document.createElement('small');
    help.className = 'richtext-help';
    help.textContent = options.helpText || 'Gebruik **vet**, *cursief*, - bullets en links in markdown-lite. HTML wordt als tekst getoond.';
    if(preview) editor.insertBefore(help, preview);
    else editor.appendChild(help);

    return editor;
  }

  function enhanceAll(root = document, options = {}){
    root.querySelectorAll('textarea[data-richtext-editor]').forEach(textarea => enhanceTextarea(textarea, options));
  }

  window.KadenaRichTextEditor = { enhanceTextarea, enhanceAll };

  document.addEventListener('DOMContentLoaded', () => enhanceAll(document));
})();
