const data = {
  portal: [
    { title: 'naver', url: 'https://www.naver.com' },
    { title: 'google', url: 'https://www.google.com' }
  ],
  exchange: [
    { title: 'binance', url: 'https://www.binance.com' },
    { title: 'kucoin', url: 'https://www.kucoin.com' }
  ]
};

const sectionsEl = document.getElementById('sections');

function render() {
  sectionsEl.innerHTML = '';
  Object.entries(data).forEach(([sectionName, bookmarks]) => {
    const sectionDiv = document.createElement('div');
    sectionDiv.className = 'section';

    const h2 = document.createElement('h2');
    h2.textContent = sectionName;
    sectionDiv.appendChild(h2);

    const ol = document.createElement('ol');

    bookmarks.forEach((bookmark, index) => {
      const li = document.createElement('li');

      const link = document.createElement('a');
      link.href = bookmark.url;
      link.textContent = bookmark.title;
      link.target = '_blank';
      li.appendChild(link);

      const editBtn = document.createElement('button');
      editBtn.textContent = 'Edit';
      editBtn.className = 'action-btn';
      editBtn.addEventListener('click', () => {
        const newTitle = prompt('New title:', bookmark.title);
        if (newTitle === null) return;
        const newUrl = prompt('New URL:', bookmark.url);
        if (newUrl === null) return;
        data[sectionName][index] = { title: newTitle, url: newUrl };
        render();
      });
      li.appendChild(editBtn);

      const removeBtn = document.createElement('button');
      removeBtn.textContent = 'Remove';
      removeBtn.className = 'action-btn';
      removeBtn.addEventListener('click', () => {
        data[sectionName].splice(index, 1);
        if (data[sectionName].length === 0) {
          delete data[sectionName];
        }
        render();
      });
      li.appendChild(removeBtn);

      ol.appendChild(li);
    });

    sectionDiv.appendChild(ol);
    sectionsEl.appendChild(sectionDiv);
  });
}

document.getElementById('add-form').addEventListener('submit', (e) => {
  e.preventDefault();
  const section = document.getElementById('section-input').value.trim();
  const title = document.getElementById('title-input').value.trim();
  const url = document.getElementById('url-input').value.trim();
  if (!data[section]) {
    data[section] = [];
  }
  data[section].push({ title, url });
  e.target.reset();
  render();
});

render();
