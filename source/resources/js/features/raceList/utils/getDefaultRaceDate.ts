/**
 * 直近の土曜日または日曜日を返す。
 * 今日が土日ならば今日、それ以外は直前の日曜日を返す。
 */
export function getDefaultRaceDate(): string {
	const today = new Date();
	const day = today.getDay(); // 0=Sun, 6=Sat

	if (day === 0 || day === 6) {
		return formatDate(today);
	}

	const sunday = new Date(today);
	sunday.setDate(today.getDate() - day);
	return formatDate(sunday);
}

function formatDate(date: Date): string {
	const y = date.getFullYear();
	const m = String(date.getMonth() + 1).padStart(2, "0");
	const d = String(date.getDate()).padStart(2, "0");
	return `${y}-${m}-${d}`;
}
