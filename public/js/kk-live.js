window.kkLive = {
  connect(url, onData) {
    if (!window.EventSource) return null;
    const start = (after) => {
      const es = new EventSource(url + (url.includes('?') ? '&' : '?') + 'after=' + (after || 0));
      es.onmessage = (ev) => {
        try {
          const data = JSON.parse(ev.data || '{}');
          onData(data);
        } catch (e) {}
      };
      es.onerror = () => {
        es.close();
        setTimeout(() => start(after), 2500);
      };
      return es;
    };
    return start(0);
  }
};
