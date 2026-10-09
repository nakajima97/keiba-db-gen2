export type HorseSearchItem = {
	uid: string;
	name: string;
	birth_year: number | null;
};

export type HorseSearchProps = {
	horses: HorseSearchItem[];
	keyword: string;
	searchedKeyword: string | null;
	onKeywordChange: (keyword: string) => void;
	onSearch: () => void;
};
