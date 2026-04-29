import type { InertiaLinkProps } from "@inertiajs/react";
import { clsx } from "clsx";
import type { ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

export const cn = (...inputs: ClassValue[]) => {
	return twMerge(clsx(inputs));
};

export const toUrl = (url: NonNullable<InertiaLinkProps["href"]>): string => {
	return typeof url === "string" ? url : url.url;
};
