import { Head, router, usePage } from "@inertiajs/react";
import { useState } from "react";
import HorseSearch from "@/features/horseSearch/presentational/HorseSearch";
import type { HorseSearchItem } from "@/features/horseSearch/presentational/HorseSearch/types";
import { index as horsesIndex } from "@/routes/horses";

type HorsesIndexProps = {
	horses: HorseSearchItem[];
	filters: {
		keyword: string | null;
	};
};

const HorsesIndex = () => {
	const { horses, filters } = usePage<HorsesIndexProps>().props;

	const [keyword, setKeyword] = useState<string>(filters.keyword ?? "");

	const handleSearch = () => {
		const trimmedKeyword = keyword.trim();
		router.get(
			horsesIndex.url(),
			trimmedKeyword !== "" ? { keyword: trimmedKeyword } : {},
			{ preserveState: true },
		);
	};

	return (
		<>
			<Head title="競走馬検索" />
			<HorseSearch
				horses={horses}
				keyword={keyword}
				searchedKeyword={filters.keyword}
				onKeywordChange={setKeyword}
				onSearch={handleSearch}
			/>
		</>
	);
};

export default HorsesIndex;
