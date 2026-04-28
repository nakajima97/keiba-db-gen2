import AuthLayoutTemplate from "@/layouts/auth/auth-simple-layout";

const AuthLayout = ({
	title = "",
	description = "",
	children,
}: {
	title?: string;
	description?: string;
	children: React.ReactNode;
}) => {
	return (
		<AuthLayoutTemplate title={title} description={description}>
			{children}
		</AuthLayoutTemplate>
	);
};

export default AuthLayout;
