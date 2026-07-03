import { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <line x1="3" y1="18" x2="21" y2="18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            <line x1="7" y1="18" x2="7" y2="11" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            <line x1="12" y1="18" x2="12" y2="6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            <line x1="17" y1="18" x2="17" y2="9" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        </svg>
    );
}
