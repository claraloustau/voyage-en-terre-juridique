(function () {
	const body = document.body;
	if (!body.classList.contains("book-site")) {
		return;
	}

	const openNotePanels = new Set();
	let bookNoteZ = 200;

	const bookHeader = document.querySelector(".book-header");
	function syncBookHeaderHeight() {
		if (!bookHeader) {
			return;
		}
		const h = Math.ceil(bookHeader.getBoundingClientRect().height);
		document.documentElement.style.setProperty("--book-header-h", `${h}px`);
	}
	syncBookHeaderHeight();
	window.addEventListener("resize", syncBookHeaderHeight);
	if (typeof ResizeObserver !== "undefined" && bookHeader) {
		new ResizeObserver(syncBookHeaderHeight).observe(bookHeader);
	}

	const btn = document.getElementById("book-toc-toggle");
	const aside = document.getElementById("book-aside");

	const mq = window.matchMedia("(min-width: 64rem)");
	const storageKey = "book-toc-collapsed-desktop";

	let setCollapsed = function () {};
	if (btn && aside) {
		setCollapsed = function (collapsed) {
			body.classList.toggle("book--toc-collapsed", collapsed);
			btn.setAttribute("aria-expanded", (!collapsed).toString());
			aside.setAttribute("aria-hidden", collapsed.toString());
		};

		function init() {
			if (mq.matches) {
				const saved = localStorage.getItem(storageKey);
				if (saved === "1") {
					setCollapsed(true);
				} else {
					setCollapsed(false);
				}
			} else {
				setCollapsed(true);
			}
		}

		init();

		mq.addEventListener("change", () => {
			if (mq.matches) {
				const saved = localStorage.getItem(storageKey);
				setCollapsed(saved === "1");
			} else {
				setCollapsed(true);
			}
		});

		btn.addEventListener("click", (e) => {
			e.stopPropagation();
			const next = !body.classList.contains("book--toc-collapsed");
			setCollapsed(next);
			if (mq.matches) {
				localStorage.setItem(storageKey, next ? "1" : "0");
			}
		});

		aside.addEventListener("click", (e) => e.stopPropagation());
	}

	/* Sommaire : chapitres repliables */
	(function initBookTocChapters() {
		const storageKey = "book-toc-chapter-expanded";
		/** @returns {Record<string, boolean>} */
		function getSaved() {
			try {
				return JSON.parse(localStorage.getItem(storageKey) || "{}");
			} catch {
				return {};
			}
		}
		/** @param {string} id
		 *  @param {boolean} expanded */
		function setSaved(id, expanded) {
			const o = getSaved();
			o[id] = expanded;
			try {
				localStorage.setItem(storageKey, JSON.stringify(o));
			} catch {
				// ignore
			}
		}
		/** @param {HTMLElement} li
		 *  @param {boolean} expanded */
		function applyTocChapterState(li, expanded) {
			const ol = li.querySelector(".book-toc__subs");
			const btn = li.querySelector(".book-toc__toggle");
			if (!ol || !btn) {
				return;
			}
			if (expanded) {
				ol.removeAttribute("hidden");
				btn.setAttribute("aria-expanded", "true");
				btn.setAttribute("aria-label", "Replier les sous-chapitres");
				li.classList.remove("book-toc__chapter--collapsed");
			} else {
				ol.setAttribute("hidden", "");
				btn.setAttribute("aria-expanded", "false");
				btn.setAttribute("aria-label", "Déplier les sous-chapitres");
				li.classList.add("book-toc__chapter--collapsed");
			}
		}

		const list = document.querySelector(".book-toc__chapters");
		if (!list) {
			return;
		}
		const saved = getSaved();
		for (const li of list.querySelectorAll(".book-toc__chapter[data-chapter-id]")) {
			const id = li.getAttribute("data-chapter-id");
			if (id && Object.prototype.hasOwnProperty.call(saved, id)) {
				applyTocChapterState(/** @type {HTMLElement} */ (li), saved[id]);
			}
		}

		list.addEventListener("click", (e) => {
			const t = e.target;
			if (!(t instanceof Element)) {
				return;
			}
			const btn = t.closest(".book-toc__toggle");
			if (!btn || !list.contains(btn)) {
				return;
			}
			const li = btn.closest(".book-toc__chapter[data-chapter-id]");
			if (!li) {
				return;
			}
			e.stopPropagation();
			const id = li.getAttribute("data-chapter-id");
			const isOpen = btn.getAttribute("aria-expanded") === "true";
			const next = !isOpen;
			applyTocChapterState(/** @type {HTMLElement} */ (li), next);
			if (id) {
				setSaved(id, next);
			}
		});
	})();

	const searchRoot = document.getElementById("book-search");
	const searchInput = document.getElementById("book-search-input");
	const searchResults = document.getElementById("book-search-results");
	const searchDataEl = document.getElementById("book-search-data");

	let searchIndex = [];
	if (searchDataEl && searchDataEl.textContent) {
		try {
			searchIndex = JSON.parse(searchDataEl.textContent);
		} catch {
			searchIndex = [];
		}
	}

	function normalize(s) {
		return String(s)
			.toLowerCase()
			.normalize("NFD")
			.replace(/\p{M}/gu, "");
	}

	function excerpt(text, query, maxLen) {
		const n = normalize(text);
		const q = normalize(query);
		if (!q) {
			return text.slice(0, maxLen) + (text.length > maxLen ? "…" : "");
		}
		const idx = n.indexOf(q);
		if (idx === -1) {
			return text.slice(0, maxLen) + (text.length > maxLen ? "…" : "");
		}
		const start = Math.max(0, idx - 40);
		const slice = text.slice(start, start + maxLen);
		return (start > 0 ? "…" : "") + slice + (start + maxLen < text.length ? "…" : "");
	}

	function escapeRegExp(s) {
		return s.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
	}

	/** URL relative (même origine) avec paramètre de surlignage */
	function hrefWithHighlight(targetUrl, query) {
		const trimmed = query.trim();
		const u = new URL(targetUrl, window.location.origin);
		u.searchParams.set("q", trimmed);
		if (u.origin === window.location.origin) {
			return u.pathname + u.search + u.hash;
		}
		return u.href;
	}

	function highlightInElement(root, query) {
		const trimmed = query.trim();
		if (trimmed.length < 2) {
			return;
		}
		let re;
		try {
			re = new RegExp(escapeRegExp(trimmed), "giu");
		} catch {
			re = new RegExp(escapeRegExp(trimmed), "gi");
		}
		const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
			acceptNode(node) {
				const el = node.parentElement;
				if (!el) {
					return NodeFilter.FILTER_REJECT;
				}
				const tag = el.tagName;
				if (
					tag === "SCRIPT" ||
					tag === "STYLE" ||
					tag === "NOSCRIPT" ||
					tag === "TEXTAREA"
				) {
					return NodeFilter.FILTER_REJECT;
				}
				if (el.closest && el.closest("mark.book-search-highlight")) {
					return NodeFilter.FILTER_REJECT;
				}
				return NodeFilter.FILTER_ACCEPT;
			},
		});
		const textNodes = [];
		let n;
		while ((n = walker.nextNode())) {
			textNodes.push(n);
		}
		for (const textNode of textNodes) {
			if (!textNode.parentNode) {
				continue;
			}
			const text = textNode.nodeValue;
			if (!text || !re.test(text)) {
				re.lastIndex = 0;
				continue;
			}
			re.lastIndex = 0;
			const frag = document.createDocumentFragment();
			let last = 0;
			let m;
			while ((m = re.exec(text)) !== null) {
				if (m.index > last) {
					frag.appendChild(
						document.createTextNode(text.slice(last, m.index)),
					);
				}
				const mark = document.createElement("mark");
				mark.className = "book-search-highlight";
				mark.appendChild(document.createTextNode(m[0]));
				frag.appendChild(mark);
				last = m.index + m[0].length;
				if (m[0].length === 0) {
					re.lastIndex++;
				}
			}
			if (last < text.length) {
				frag.appendChild(document.createTextNode(text.slice(last)));
			}
			textNode.parentNode.replaceChild(frag, textNode);
			re.lastIndex = 0;
		}
	}

	function applyHighlightFromUrl() {
		const params = new URLSearchParams(window.location.search);
		const raw = params.get("q");
		if (raw === null || raw === "") {
			return;
		}
		let decoded = raw;
		try {
			decoded = decodeURIComponent(raw.replace(/\+/g, " "));
		} catch {
			return;
		}
		const main = document.getElementById("book-main");
		if (!main) {
			return;
		}
		requestAnimationFrame(() => {
			highlightInElement(main, decoded);
			const first = main.querySelector(".book-search-highlight");
			if (first) {
				first.scrollIntoView({ behavior: "smooth", block: "center" });
			}
		});
	}

	let searchTimer = null;
	const maxResults = 20;

	function renderResults(query) {
		if (!searchResults || !searchInput) {
			return;
		}
		const q = query.trim();
		if (q.length < 2) {
			searchResults.innerHTML = "";
			searchResults.hidden = true;
			return;
		}
		const nq = normalize(q);
		const hits = [];
		for (let i = 0; i < searchIndex.length; i++) {
			const item = searchIndex[i];
			const hay = normalize(
				[item.title, item.chapter, item.text].filter(Boolean).join(" "),
			);
			if (hay.includes(nq)) {
				hits.push(item);
				if (hits.length >= maxResults) {
					break;
				}
			}
		}
		if (hits.length === 0) {
			searchResults.innerHTML =
				'<p class="book-search__empty" role="status">' +
				"Aucun résultat." +
				"</p>";
			searchResults.hidden = false;
			return;
		}
		const frag = document.createDocumentFragment();
		for (const item of hits) {
			const a = document.createElement("a");
			a.className = "book-search__hit";
			a.href = hrefWithHighlight(item.url, q);
			a.setAttribute("role", "option");
			const title = document.createElement("span");
			title.className = "book-search__hit-title";
			title.textContent = item.title;
			a.appendChild(title);
			if (item.chapter) {
				const ch = document.createElement("span");
				ch.className = "book-search__hit-chapter";
				ch.textContent = item.chapter;
				a.appendChild(ch);
			}
			const ex = document.createElement("span");
			ex.className = "book-search__hit-excerpt";
			ex.textContent = excerpt(item.text || "", q, 140);
			a.appendChild(ex);
			frag.appendChild(a);
		}
		searchResults.innerHTML = "";
		searchResults.appendChild(frag);
		searchResults.hidden = false;
	}

	function scheduleSearch() {
		if (!searchInput) {
			return;
		}
		clearTimeout(searchTimer);
		searchTimer = window.setTimeout(() => {
			renderResults(searchInput.value);
		}, 180);
	}

	if (searchInput && searchResults && searchRoot) {
		searchInput.addEventListener("input", scheduleSearch);
		searchInput.addEventListener("focus", () => {
			if (searchInput.value.trim().length >= 2) {
				renderResults(searchInput.value);
			}
		});
		searchResults.addEventListener("click", (e) => e.stopPropagation());
		searchRoot.addEventListener("click", (e) => e.stopPropagation());
	}

	document.addEventListener("click", (e) => {
		const t = e.target;
		if (mq.matches || body.classList.contains("book--toc-collapsed")) {
			if (searchResults && !searchResults.hidden && searchRoot) {
				if (t instanceof Node && !searchRoot.contains(t)) {
					searchResults.hidden = true;
				}
			}
			return;
		}
		if (!btn || !aside) {
			return;
		}
		if (t instanceof Node && (aside.contains(t) || btn.contains(t))) {
			return;
		}
		if (t instanceof Node && searchRoot && searchRoot.contains(t)) {
			return;
		}
		setCollapsed(true);
		if (searchResults && !searchResults.hidden) {
			searchResults.hidden = true;
		}
	});

	document.addEventListener("keydown", (e) => {
		if (e.key !== "Escape") {
			return;
		}
		/* Fermer d’abord le panneau de note le plus au-dessus */
		if (openNotePanels.size > 0) {
			bookNoteCloseTopmost();
			e.preventDefault();
			return;
		}
		if (searchResults && !searchResults.hidden) {
			searchResults.hidden = true;
			return;
		}
		if (
			btn &&
			aside &&
			!mq.matches &&
			!body.classList.contains("book--toc-collapsed")
		) {
			setCollapsed(true);
		}
	});

	applyHighlightFromUrl();

	/* Notes : panneaux flottants multiples, déplaçables ; re-clic sur le terme = fermer */

	/** Même contenu qu’en PHP, sans se fier au DOM (p interdit dans un span) */
	function noteHtmlFromPayload(trigger) {
		const b64 = trigger.getAttribute("data-book-note-payload");
		if (!b64 || !b64.trim()) {
			return null;
		}
		try {
			const bin = atob(b64.trim().replace(/[\s\n\r]+/g, ""));
			const bytes = new Uint8Array(bin.length);
			for (let i = 0; i < bin.length; i++) {
				bytes[i] = bin.charCodeAt(i);
			}
			return new TextDecoder("utf-8").decode(bytes);
		} catch {
			return null;
		}
	}

	/** Caché à côté du déclencheur : repli si payload absent */
	function resolveNoteSourceEl(trigger) {
		const sib = trigger.nextElementSibling;
		if (sib instanceof Element && sib.classList.contains("book-annot__tip")) {
			return sib;
		}
		const id = trigger.getAttribute("data-book-note");
		return id ? document.getElementById(id) : null;
	}

	function bookNotePanelCloseForTrigger(trigger) {
		const panel = trigger._bookNotePanel;
		if (panel && openNotePanels.has(panel)) {
			openNotePanels.delete(panel);
		}
		if (panel && panel.isConnected) {
			panel.remove();
		}
		trigger._bookNotePanel = null;
		trigger.setAttribute("aria-expanded", "false");
		if (trigger.hasAttribute("aria-controls")) {
			trigger.removeAttribute("aria-controls");
		}
	}

	function bookNotePanelCloseByEl(panel) {
		const t = panel._bookNoteTrigger;
		if (t) {
			bookNotePanelCloseForTrigger(t);
		} else {
			if (openNotePanels.has(panel)) {
				openNotePanels.delete(panel);
			}
			panel.remove();
		}
	}

	function bookNoteCloseTopmost() {
		let best = null;
		let bestZ = -1;
		for (const p of openNotePanels) {
			if (!p.isConnected) {
				openNotePanels.delete(p);
				continue;
			}
			const z = parseInt(p.style.zIndex, 10) || 0;
			if (z > bestZ) {
				bestZ = z;
				best = p;
			}
		}
		if (best) {
			bookNotePanelCloseByEl(best);
		}
	}

	function makeBookNoteDraggable(panel, head) {
		const dragClass = "is-book-note-dragging";
		let startX;
		let startY;
		let startLeft;
		let startTop;
		let onMove;
		let onUp;

		head.addEventListener("pointerdown", (e) => {
			if (e.button !== 0) {
				return;
			}
			if (e.target.closest?.("[data-book-note-close]")) {
				return;
			}
			const r = panel.getBoundingClientRect();
			const cs = getComputedStyle(panel);
			if (cs.position !== "fixed") {
				return;
			}
			const curLeft = r.left;
			const curTop = r.top;
			panel.style.left = `${curLeft}px`;
			panel.style.top = `${curTop}px`;
			panel.style.right = "auto";
			panel.style.bottom = "auto";
			panel.style.margin = "0";
			e.preventDefault();
			startX = e.clientX;
			startY = e.clientY;
			startLeft = curLeft;
			startTop = curTop;
			head.classList.add(dragClass);
			if (e.pointerId !== undefined) {
				try {
					head.setPointerCapture(e.pointerId);
				} catch {
					// ignore
				}
			}
			onMove = (ev) => {
				const dx = ev.clientX - startX;
				const dy = ev.clientY - startY;
				const w = panel.getBoundingClientRect().width;
				const h = panel.getBoundingClientRect().height;
				const maxL = window.innerWidth - w;
				const maxT = window.innerHeight - h;
				const left = Math.max(0, Math.min(startLeft + dx, maxL));
				const top = Math.max(0, Math.min(startTop + dy, maxT));
				panel.style.left = `${left}px`;
				panel.style.top = `${top}px`;
			};
			onUp = () => {
				head.classList.remove(dragClass);
				if (e.pointerId !== undefined) {
					try {
						head.releasePointerCapture(e.pointerId);
					} catch {
						// ignore
					}
				}
				document.removeEventListener("pointermove", onMove, true);
				document.removeEventListener("pointerup", onUp, true);
				document.removeEventListener("pointercancel", onUp, true);
			};
			document.addEventListener("pointermove", onMove, true);
			document.addEventListener("pointerup", onUp, true);
			document.addEventListener("pointercancel", onUp, true);
		});
	}

	const NOTE_RESIZE_MIN_W = 200;
	const NOTE_RESIZE_MIN_H = 100;

	/** Poignée bas-droit : largeur / hauteur explicites */
	function makeBookNoteResizable(panel, handle) {
		let sW;
		let sH;
		let m0x;
		let m0y;
		let onMoveR;
		let onUpR;
		let downE;

		handle.addEventListener("pointerdown", (e) => {
			if (e.button !== 0) {
				return;
			}
			e.preventDefault();
			e.stopPropagation();
			downE = e;
			const r = panel.getBoundingClientRect();
			if (!panel.style.width) {
				panel.style.width = `${r.width}px`;
			}
			if (!panel.style.height) {
				panel.style.height = `${r.height}px`;
			}
			panel.style.maxWidth = "none";
			panel.style.maxHeight = "none";
			sW = r.width;
			sH = r.height;
			m0x = e.clientX;
			m0y = e.clientY;
			if (e.pointerId !== undefined) {
				try {
					handle.setPointerCapture(e.pointerId);
				} catch {
					// ignore
				}
			}
			const maxW = () => window.innerWidth - 12;
			const maxH = () => window.innerHeight - 12;
			onMoveR = (ev) => {
				let w = sW + (ev.clientX - m0x);
				let h = sH + (ev.clientY - m0y);
				w = Math.max(
					NOTE_RESIZE_MIN_W,
					Math.min(w, maxW()),
				);
				h = Math.max(
					NOTE_RESIZE_MIN_H,
					Math.min(h, maxH()),
				);
				panel.style.width = `${w}px`;
				panel.style.height = `${h}px`;
			};
			onUpR = () => {
				if (downE && downE.pointerId !== undefined) {
					try {
						handle.releasePointerCapture(downE.pointerId);
					} catch {
						// ignore
					}
				}
				document.removeEventListener("pointermove", onMoveR, true);
				document.removeEventListener("pointerup", onUpR, true);
				document.removeEventListener("pointercancel", onUpR, true);
				downE = null;
			};
			document.addEventListener("pointermove", onMoveR, true);
			document.addEventListener("pointerup", onUpR, true);
			document.addEventListener("pointercancel", onUpR, true);
		});
	}

	/** @returns {string} HTML ou chaîne vide */
	function getNoteContentHtml(trigger, source) {
		const fromPayload = (noteHtmlFromPayload(trigger) || "").trim();
		if (fromPayload) {
			return fromPayload;
		}
		if (!source) {
			return "";
		}
		const fromHtml = (source.innerHTML || "").trim();
		if (fromHtml) {
			return source.innerHTML;
		}
		const fromText = (source.textContent || "").trim();
		if (fromText) {
			return `<p>${escapeTextForNote(fromText)}</p>`;
		}
		return "";
	}

	function escapeTextForNote(s) {
		return String(s)
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;");
	}

	/** @param {object} o @param {Element} o.trigger */
	function bookNoteOpenWithPanel(o) {
		const { trigger } = o;
		const source = resolveNoteSourceEl(trigger);
		const html = getNoteContentHtml(trigger, source);
		if (!html.trim()) {
			return;
		}

		bookNoteZ += 1;
		const kickerId = `book-note-kicker-${Date.now()}-${bookNoteZ}`;
		const panelId = `book-note-wrap-${kickerId}`;

		const panel = document.createElement("div");
		panel.id = panelId;
		panel.className = "book-note-panel";
		panel.setAttribute("role", "dialog");
		panel.setAttribute("aria-modal", "false");
		panel.setAttribute("aria-labelledby", kickerId);
		panel.style.zIndex = String(bookNoteZ);
		panel._bookNoteTrigger = trigger;

		const inner = document.createElement("div");
		inner.className = "book-note-panel__inner";
		inner.setAttribute("tabindex", "-1");

		const head = document.createElement("div");
		head.className =
			"book-note-backdrop__head book-note-backdrop__head--draggable";
		const k = document.createElement("p");
		k.id = kickerId;
		k.className = "book-note-backdrop__kicker";
		const termLabel = (trigger.textContent || "")
			.replace(/\s+/g, " ")
			.trim();
		k.textContent = termLabel || "Note";

		const dragHint = document.createElement("span");
		dragHint.className = "book-note-backdrop__drag-hint";
		dragHint.setAttribute("title", "Déplacer — glisser la barre de titre");
		dragHint.setAttribute("aria-hidden", "true");
		dragHint.innerHTML =
			'<svg class="book-note-backdrop__drag-ico" width="18" height="18" viewBox="0 0 18 18" focusable="false" xmlns="http://www.w3.org/2000/svg"><circle cx="5" cy="4" r="1.35" fill="currentColor"/><circle cx="9" cy="4" r="1.35" fill="currentColor"/><circle cx="5" cy="8.5" r="1.35" fill="currentColor"/><circle cx="9" cy="8.5" r="1.35" fill="currentColor"/><circle cx="5" cy="13" r="1.35" fill="currentColor"/><circle cx="9" cy="13" r="1.35" fill="currentColor"/></svg>';

		const closeBtn = document.createElement("button");
		closeBtn.type = "button";
		closeBtn.className = "book-note-backdrop__close";
		closeBtn.setAttribute("data-book-note-close", "");
		closeBtn.setAttribute("aria-label", "Fermer");
		closeBtn.textContent = "×";

		const headActions = document.createElement("div");
		headActions.className = "book-note-backdrop__head-actions";
		headActions.appendChild(dragHint);
		headActions.appendChild(closeBtn);

		const content = document.createElement("div");
		content.className = "book-note-backdrop__content book-prose";
		if (html.trim()) {
			content.innerHTML = html;
		}
		head.appendChild(k);
		head.appendChild(headActions);
		inner.appendChild(head);
		inner.appendChild(content);
		panel.appendChild(inner);
		const resizeH = document.createElement("div");
		resizeH.className = "book-note-resize";
		resizeH.setAttribute("data-book-note-resize", "");
		resizeH.setAttribute("title", "Redimensionner en tirant l’angle");
		resizeH.setAttribute("aria-hidden", "true");
		resizeH.innerHTML =
			'<svg class="book-note-resize-ico" width="16" height="16" viewBox="0 0 12 12" focusable="false" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill="currentColor" d="M12 12H8.2L12 8.2V12zm0-4.2L4.2 12H6l6-6V7.8zM12 3.5L1.5 12H3.2L12 3.2V3.5z" opacity="0.65"/></svg>';
		panel.appendChild(resizeH);
		document.body.appendChild(panel);
		openNotePanels.add(panel);
		trigger._bookNotePanel = panel;
		trigger.setAttribute("aria-expanded", "true");
		trigger.setAttribute("aria-controls", panelId);
		/* Position initiale : biais gauche, léger offset si plusieurs */
		const stack = openNotePanels.size;
		const idx = stack - 1;
		requestAnimationFrame(() => {
			const w = panel.getBoundingClientRect().width;
			const h = panel.getBoundingClientRect().height;
			const bias = 16 + 0.015 * window.innerWidth;
			let left = (window.innerWidth - w) / 2 - bias + idx * 20;
			let top = window.innerHeight * 0.25 + idx * 28;
			const maxL = window.innerWidth - w;
			const maxT = window.innerHeight - h;
			left = Math.max(8, Math.min(left, maxL - 8));
			top = Math.max(8, Math.min(top, maxT - 8));
			panel.style.left = `${left}px`;
			panel.style.top = `${top}px`;
		});
		makeBookNoteDraggable(panel, head);
		makeBookNoteResizable(panel, resizeH);
		closeBtn.addEventListener("click", (ev) => {
			ev.stopPropagation();
			bookNotePanelCloseForTrigger(trigger);
		});
		requestAnimationFrame(() => {
			closeBtn.focus();
		});
	}

	/** Avant : ancienne modale plein écran (nettoyage) */
	(function bookNoteRemoveLegacy() {
		const el = document.getElementById("book-note-backdrop");
		if (el) {
			el.remove();
		}
	})();

	function bookNoteOpen(trigger) {
		if (trigger._bookNotePanel && trigger._bookNotePanel.isConnected) {
			bookNotePanelCloseForTrigger(trigger);
			trigger.focus();
			return;
		}
		const source = resolveNoteSourceEl(trigger);
		if (!getNoteContentHtml(trigger, source).trim()) {
			return;
		}
		bookNoteOpenWithPanel({ trigger });
	}

	document.addEventListener(
		"click",
		(e) => {
			if (!(e.target instanceof Element) || e.button !== 0) {
				return;
			}
			const t = e.target.closest(".book-annot");
			if (!t) {
				return;
			}
			e.preventDefault();
			bookNoteOpen(t);
		},
		true,
	);

	document.addEventListener("keydown", (e) => {
		if (e.key !== "Enter" && e.key !== " ") {
			return;
		}
		const t = e.target;
		if (!(t instanceof Element) || !t.classList.contains("book-annot")) {
			return;
		}
		e.preventDefault();
		bookNoteOpen(t);
	}, true);
})();

